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

namespace ImaginationMedia\CurbsidePickup\Cron;

use ImaginationMedia\CurbsidePickup\Action\EmailNotificationInterface;
use ImaginationMedia\CurbsidePickup\Helper\Data;
use ImaginationMedia\CurbsidePickup\Setup\Patch\Data\OrderStatus;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Psr\Log\LoggerInterface;

class SendDeliveryReminder
{
    /**
     * @var CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var Data
     */
    private $curbsidePickupHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EmailNotificationInterface
     */
    private EmailNotificationInterface $emailNotification;

    /**
     * @param EmailNotificationInterface $emailNotification
     * @param Data $curbsidePickupHelper
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        EmailNotificationInterface $emailNotification,
        Data $curbsidePickupHelper,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger
    ) {
        $this->orderCollectionFactory = $collectionFactory;
        $this->curbsidePickupHelper = $curbsidePickupHelper;
        $this->emailNotification = $emailNotification;
        $this->logger = $logger;
    }

    /**
     * Send reminder email 1 hour before delivery (only if pickup scheduled)
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->curbsidePickupHelper->isScheduledPickupEnabled()
          || !$this->curbsidePickupHelper->isEmailNotificationsEnabled()
        ) {
            return;
        }

        $currentDateFormatted = new \DateTime('now');
        $oneHour = new \DateInterval('PT1H');
        $targetDeliveryDateFormatted = $currentDateFormatted->add($oneHour)->format('Y-m-d H:i');

        /** @var $orders Collection */
        $orders = $this->orderCollectionFactory->create()
            ->addFieldToFilter('curbside', OrderStatus::STATUS_ACTIVE)
            ->addFieldToFilter('status', ['eq' => OrderStatus::STATUS_CUSTOMER_READY])
            ->addFieldToFilter('curbside_delivery_time', ['neq' => null]);

        $numberEmailsSent = 0;
        if ($orders->getSize() < 1) {
            return;
        }

        foreach ($orders->getItems() as $order) {
            /** @var Order $order */

            $checkDeliveryTime = (new \DateTime($order->getCurbsideDeliveryTime()))->format('Y-m-d H:i');
            if ($checkDeliveryTime !== $targetDeliveryDateFormatted) {
                continue;
            }
            if ($this->emailNotification->sendPickupReminder($order)) {
                $numberEmailsSent++;
            } else {
                $this->logger->error('Email send failed for Order #' . $order->getEntityId());
            }
        }
        $this->logger->info("Number of mails sent for deliveries in next hour: " . $numberEmailsSent);
    }
}
