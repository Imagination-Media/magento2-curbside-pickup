<?php

/**
 * Curbside pickup for Magento 2
 *
 * This module extends the base pick up in-store Magento 2 functionality
 * adding an option for a curbside pick up
 *
 * @package ImaginationMedia\CurbsidePickup
 * @author Igor Ludgero Miura <igor@imaginationmedia.com>
 * @copyright Copyright (c) 2020 Imagination Media (https://www.imaginationmedia.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

declare(strict_types=1);

namespace ImaginationMedia\CurbsidePickup\Observer\Quote\PlaceOrder;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

/**
 * Class SaveCurbsideInfo
 * @package ImaginationMedia\CurbsidePickup\Observer\Quote\PlaceOrder
 */
class SaveCurbsideInfo implements ObserverInterface
{

    public const FIELD_CURBSIDE               = "curbside";
    public const FIELD_CURBSIDE_DATA          = "curbside_data";
    public const FIELD_CURBSIDE_DELIVERY_TIME = "curbside_delivery_time";

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

        if ($quote->getData(self::FIELD_CURBSIDE)) {
            $order->setData(self::FIELD_CURBSIDE, true);
            $order->setData(self::FIELD_CURBSIDE_DATA, $quote->getData(self::FIELD_CURBSIDE_DATA));
            $order->setData(self::FIELD_CURBSIDE_DELIVERY_TIME, $quote->getData(self::FIELD_CURBSIDE_DELIVERY_TIME));
        }
    }
}
