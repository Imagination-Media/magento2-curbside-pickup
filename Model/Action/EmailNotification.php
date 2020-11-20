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
     * EmailNotification constructor.
     * @param CurbsideOrderSenderFactory $curbsideOrderSenderFactory
     * @param CurbsideHelper $curbsideHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        CurbsideOrderSenderFactory $curbsideOrderSenderFactory,
        CurbsideHelper $curbsideHelper,
        LoggerInterface $logger
    ) {
        $this->curbsideHelper = $curbsideHelper;
        $this->curbsideOrderSenderFactory = $curbsideOrderSenderFactory;
        $this->logger = $logger;
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
                ->send($order);
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
                ->send($order);
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
                ->send($order);
        } catch (\Exception $e) {
            $this->logger->error(
                'Order Accepted Email failed for Order #' . $order->getEntityId(),
                ['error' => $e->getMessage()]
            );
        }
        return false;
    }
}
