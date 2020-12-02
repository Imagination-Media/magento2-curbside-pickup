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

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Exception\CouldNotShipException;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use ImaginationMedia\CurbsidePickup\Action\CurbsideOrderInterface;

class Deliver  extends Action implements HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'ImaginationMedia_CurbsidePickup::actions_deliver';

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
     * @param Action\Context $context
     * @param CurbsideOrderInterface $curbsideOrderService
     * @param OrderRepositoryInterface $orderRepository
     * @param JsonFactory $resultJsonFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Action\Context $context,
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
                Order::STATE_COMPLETE,
                $order,
                'Order Completed. Curbside Order Delivered with reference'
            );

            /** @var Order\Shipment $shipment */
            $shipment = $this->curbsideOrderService->doShipment($order);
            $this->curbsideOrderService->clearPickupTokenForOrder($order);

            $response = [
                'success' => 'true',
                'status' => $order->getStatus(),
                'message' => __('Order has been marked as Shipped. Shipment ID: #%1 . Order Status update: delivered to customer.', $shipment->getEntityId())
            ];
        } catch (CouldNotShipException $e) {
            $this->logger->error($e->getMessage());
            $response = [
                'error' => 'true',
                'message' => __('Error occurred on shipping Order Id %1.', $orderId)
            ];
        } catch (NoSuchEntityException $e) {
            $this->logger->error($e->getMessage());
            $response = [
                'error' => 'true',
                'message' => __('No Order with Id %1 found.', $orderId)
            ];
        } catch (\Exception $e) {
            $this->logger->error($e->getTraceAsString());
            $response = [
                'error' => 'true',
                'message' => __('Order has been completed successfully.')
            ];
        }

        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);

        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
