<?php

/**
 * Curbside pickup for Magento 2
 *
 * This module extends the base pick up in-store Magento 2 functionality
 * adding an option for a curbside pick up
 *
 * @package ImaginationMedia\CurbsidePickup
 * @author Igor Ludgero Miura <igor@imaginationmedia.com>
 * @author Antonio Lolić <antonio@imaginationmedia.com>
 * @copyright Copyright (c) 2020 Imagination Media (https://www.imaginationmedia.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

declare(strict_types=1);

namespace ImaginationMedia\CurbsidePickup\Observer\Quote\PlaceOrder;

use ImaginationMedia\CurbsidePickup\Model\CurbsideOrder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use ImaginationMedia\CurbsidePickup\Model\CurbsidePickupTokenManagementInterface;

/**
 * Class SaveCurbsideInfo
 * @package ImaginationMedia\CurbsidePickup\Observer\Quote\PlaceOrder
 */
class SaveCurbsideInfo implements ObserverInterface
{
    /**
     * @var CurbsidePickupTokenManagementInterface
     */
    private CurbsidePickupTokenManagementInterface $pickupTokenManagement;

    /**
     * SaveCurbsideInfo constructor.
     * @param CurbsidePickupTokenManagementInterface $pickupTokenManagement
     */
    public function __construct(CurbsidePickupTokenManagementInterface $pickupTokenManagement)
    {
        $this->pickupTokenManagement = $pickupTokenManagement;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /**
         * @var $quote Quote
         * @var $order Order
         */
        $quote = $observer->getData("quote");
        $order = $observer->getData("order");

        if ($quote->getData(CurbsideOrder::FIELD_CURBSIDE)) {
            $order->setData(CurbsideOrder::FIELD_CURBSIDE, true);
            $order->setData(CurbsideOrder::FIELD_CURBSIDE_DATA, $quote->getData(CurbsideOrder::FIELD_CURBSIDE_DATA));
            $order->setData(CurbsideOrder::FIELD_CURBSIDE_DELIVERY_TIME, $quote->getData(CurbsideOrder::FIELD_CURBSIDE_DELIVERY_TIME));

            $token = $this->pickupTokenManagement->generateToken();
            $order->setData(CurbsideOrder::FIELD_CURBSIDE_PICKUP_TOKEN, $token);
        }
    }
}
