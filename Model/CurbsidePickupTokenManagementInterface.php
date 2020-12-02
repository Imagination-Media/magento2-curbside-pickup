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

namespace ImaginationMedia\CurbsidePickup\Model;

use Magento\Sales\Model\Order;

interface CurbsidePickupTokenManagementInterface
{
    /**
     * @return string
     */
    public function generateToken(): string;

    /**
     * @param string $token
     * @param Order $order
     * @return bool
     */
    public function isValidPickupTokenForOrder(string $token, Order $order): bool;
}
