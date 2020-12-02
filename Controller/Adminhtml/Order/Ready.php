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

use ImaginationMedia\CurbsidePickup\Action\EmailNotificationInterface;
use ImaginationMedia\CurbsidePickup\Exception\EmailNotificationException;
use ImaginationMedia\CurbsidePickup\Setup\Patch\Data\OrderStatus;
use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use ImaginationMedia\CurbsidePickup\Action\CurbsideOrderInterface;

class Ready extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'ImaginationMedia_CurbsidePickup::actions_ready';

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
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
        EmailNotificationInterface $emailNotification,
        CurbsideOrderInterface $curbsideOrderService,
        OrderRepositoryInterface $orderRepository,
        JsonFactory $resultJsonFactory,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->orderRepository = $orderRepository;
        $this->logger = $logger;
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
                OrderStatus::STATUS_READY_TO_PICK_UP,
                $order,
                'Curbside Order Ready for Pickup with reference'
            );

            if (!$this->emailNotification->notifyOrderReadyForPickup($order)) {
                throw new EmailNotificationException(__('Curbside Order Ready Email notification failed.'));
            }

            $response = [
                'success' => 'true',
                'status' => $order->getStatus(),
                'message' => __('Order Status update: ready for pickup.')
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
}
