<?php
/**
 * Curbside pickup for Magento 2
 *
 * This module extends the base pick up in-store Magento 2 functionality
 * adding an option for a curbside pick up
 *
 * @package ImaginationMedia\CurbsidePickup
 * @author Antonio Lolić <antonio@imaginationmedia.com>
 * @copyright Copyright (c) 2020 Imagination Media (https://www.imaginationmedia.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

declare(strict_types=1);

namespace ImaginationMedia\CurbsidePickup\Controller\Adminhtml\Order;

use ImaginationMedia\CurbsidePickup\Exception\EmailNotificationException;
use ImaginationMedia\CurbsidePickup\Setup\Patch\Data\OrderStatus;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Exception\CouldNotInvoiceException;
use Magento\Framework\DB\TransactionFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use ImaginationMedia\CurbsidePickup\Action\CurbsideOrderInterface;
use ImaginationMedia\CurbsidePickup\Action\EmailNotificationInterface;

class Accept  extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'ImaginationMedia_CurbsidePickup::actions_accept';

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var JsonFactory
     */
    private JsonFactory $resultJsonFactory;

    /**
     * @var TransactionFactory
     */
    private TransactionFactory $transactionFactory;

    /**
     * @var CurbsideOrderInterface
     */
    private CurbsideOrderInterface $curbsideOrderService;
    /**
     * @var EmailNotificationInterface
     */
    private EmailNotificationInterface $emailNotification;

    /**
     * @param Action\Context $context
     * @param EmailNotificationInterface $emailNotification
     * @param CurbsideOrderInterface $curbsideOrderService
     * @param OrderRepositoryInterface $orderRepository
     * @param TransactionFactory $transactionFactory
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        EmailNotificationInterface $emailNotification,
        CurbsideOrderInterface $curbsideOrderService,
        OrderRepositoryInterface $orderRepository,
        TransactionFactory $transactionFactory,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
        $this->transactionFactory = $transactionFactory;
        $this->curbsideOrderService = $curbsideOrderService;
        $this->emailNotification = $emailNotification;
    }

    /**
     * Ajax action for Accept Curbside Order
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getPostValue('id');
        if (!$orderId) {
            $this->messageManager->addErrorMessage(__('No ID provided for load.'));
            return $this->_redirect('*/*');
        }

        try {
            /** @var Order $order */
            $order = $this->orderRepository->get($orderId);
            $order = $this->curbsideOrderService->updateStatus(
                OrderStatus::STATUS_ACCEPTED,
                $order,
                'Curbside Order accepted'
            );

            /** @var Order\Invoice $invoice */
            $invoice = $this->curbsideOrderService->doInvoice($order);
            $order = $this->syncOrderStatusHistory($order, $invoice);

            if (!$this->emailNotification->notifyOrderAccepted($order)) {
                throw new EmailNotificationException(__('Curbside Order Accepted Email notification failed.'));
            }

            $response = [
                'success' => 'true',
                'status' => $order->getStatus(),
                'message' => __('Order is successfully invoiced. Invoice ID: #%1. Order Status: accepted.', $invoice->getEntityId())
            ];
        } catch (CouldNotInvoiceException $e) {
            $this->logger->error($e->getMessage());
            $response = [
                'error' => 'true',
                'message' => __('Order with Id %1 is already invoiced.', $orderId)
            ];
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
            $response = [
                'error' => 'true',
                'message' => __('No Order with Id %1 found.', $orderId)
            ];
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $response = [
                'error' => 'true',
                'message' => $e->getTraceAsString()
            ];
        }

        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);

        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

    /**
     * @param Order $order
     * @param \Magento\Sales\Api\Data\InvoiceInterface $invoice
     * @return Order
     */
    private function syncOrderStatusHistory(Order $order, \Magento\Sales\Api\Data\InvoiceInterface $invoice): Order
    {
        $order->addCommentToStatusHistory(__('Notified customer about invoice creation #%1.', $invoice->getEntityId()))
            ->setIsCustomerNotified(true);

        /** @var Order $order */
        $order = $this->orderRepository->save($order);
        return $order;
    }
}
