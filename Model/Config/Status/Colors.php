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

namespace ImaginationMedia\CurbsidePickup\Model\Config\Status;

use ImaginationMedia\CurbsidePickup\Setup\Patch\Data\OrderStatus;
use Magento\Sales\Model\Order;

class Colors
{
    public const ORDER_ACCEPTED_HEX = '#fcb98c';

    public const ORDER_READY_TO_PICKUP_HEX = '#aafdaa';

    public const ORDER_CUSTOMER_READY_HEX = '#4bd033';

    public const ORDER_DELIVERED_HEX = '#b9e7fd';

    public const ORDER_PENDING_HEX = '#fbe5d6';

    /**
     * @param string $status
     * @return string
     */
    public function getStatusColorClass(string $status): string
    {
        if ($status === OrderStatus::STATUS_ACCEPTED) {
            return 'curbside-accepted';
        } elseif ($status === OrderStatus::STATUS_READY_TO_PICK_UP) {
            return 'curbside-ready';
        } elseif ($status === OrderStatus::STATUS_CUSTOMER_READY) {
            return 'curbside-customer-ready';
        } elseif ($status === Order::STATE_COMPLETE) {
            return 'delivered';
        }
        return 'pending';
    }
}
