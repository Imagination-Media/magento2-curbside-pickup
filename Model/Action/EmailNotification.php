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

namespace ImaginationMedia\CurbsidePickup\Model\Action;

use ImaginationMedia\CurbsidePickup\Action\EmailNotificationInterface;
use ImaginationMedia\CurbsidePickup\Helper\Data as CurbsideHelper;
use ImaginationMedia\CurbsidePickup\Model\Email\CurbsideOrderSenderFactory;
use Magento\Sales\Api\Data\OrderInterface;
use ImaginationMedia\CurbsidePickup\Model\Mapper\CurbsideDataFactory;
use Psr\Log\LoggerInterface;

class EmailNotification implements EmailNotificationInterface
{
    /**
     * @var CurbsideHelper
     */
    private $curbsideHelper;

    /**
     * @var CurbsideOrderSenderFactory
     */
    private $curbsideOrderSenderFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CurbsideDataFactory
     */
    private $curbsideDataFactory;

    /**
     * EmailNotification constructor.
     * @param CurbsideOrderSenderFactory $curbsideOrderSenderFactory
     * @param CurbsideDataFactory $curbsideDataFactory
     * @param CurbsideHelper $curbsideHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        CurbsideOrderSenderFactory $curbsideOrderSenderFactory,
        CurbsideDataFactory $curbsideDataFactory,
        CurbsideHelper $curbsideHelper,
        LoggerInterface $logger
    ) {
        $this->curbsideHelper = $curbsideHelper;
        $this->curbsideOrderSenderFactory = $curbsideOrderSenderFactory;
        $this->logger = $logger;
        $this->curbsideDataFactory = $curbsideDataFactory;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function sendPickupReminder(OrderInterface $order): bool
    {
        try {
            $emailTemplateId = $this->curbsideHelper->getDeliveryReminderTemplateId();
            return $this->curbsideOrderSenderFactory
                ->create(['emailTemplateId' => $emailTemplateId])
                ->send($order, [
                    'order_data' => [
                        'curbside_delivery_time' => $this->getDeliveryTime($order),
                        'pickup_location_name' => $this->getPickupLocationName($order)
                    ]
                ]);
        } catch (\Exception $e) {
            $this->logger->error(
                'Pickup Reminder Email send failed for Order #' . $order->getEntityId(),
                ['error' => $e->getMessage()]
            );
        }
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function notifyOrderReadyForPickup(OrderInterface $order): bool
    {
        try {
            $emailTemplateId = $this->curbsideHelper->getReadyTemplateId();
            return $this->curbsideOrderSenderFactory
                ->create(['emailTemplateId' => $emailTemplateId])
                ->send($order, ['comment' => __('Please click "I\'m here" button once you come on location')]);
        } catch (\Exception $e) {
            $this->logger->error(
                'Order Ready To Pickup Email failed for Order #' . $order->getEntityId(),
                ['error' => $e->getMessage()]
            );
        }
        return false;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function notifyOrderAccepted(OrderInterface $order): bool
    {
        try {
            $emailTemplateId = $this->curbsideHelper->getAcceptTemplateId();
            return $this->curbsideOrderSenderFactory
                ->create(['emailTemplateId' => $emailTemplateId])
                ->send($order, ['comment' => __('Please visit order page for order schedule. You will recieve an invoice in separate email.')]);
        } catch (\Exception $e) {
            $this->logger->error(
                'Order Accepted Email failed for Order #' . $order->getEntityId(),
                ['error' => $e->getMessage()]
            );
        }
        return false;
    }

    /**
     * @param OrderInterface $order
     * @return string
     * @throws \Exception
     */
    private function getDeliveryTime(OrderInterface $order): string
    {
        return (new \DateTime($order->getCurbsideDeliveryTime()))->format('m/d/y H:i A');
    }

    /**
     * @param OrderInterface $order
     * @return string|null
     * @throws \Exception
     */
    private function getPickupLocationName(OrderInterface $order): ?string
    {
        return $this->curbsideDataFactory->create(['order' => $order])->getPickupLocationName();
    }
}
