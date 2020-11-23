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

namespace ImaginationMedia\CurbsidePickup\Action;

use Magento\Sales\Api\Data\OrderInterface;

interface EmailNotificationInterface
{
    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function sendPickupReminder(OrderInterface $order): bool;

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function notifyOrderReadyForPickup(OrderInterface $order): bool;

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function notifyOrderAccepted(OrderInterface $order): bool;
}
