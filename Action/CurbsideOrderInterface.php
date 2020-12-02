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

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\ShipmentInterface;

interface CurbsideOrderInterface
{
    /**
     * @param OrderInterface $order
     * @return null|InvoiceInterface
     * @throws \Exception
     */
    public function doInvoice(OrderInterface $order): ?InvoiceInterface;

    /**
     * @param OrderInterface $order
     * @return null|ShipmentInterface
     * @throws LocalizedException
     */
    public function doShipment(OrderInterface $order): ?ShipmentInterface;

    /**
     * @param string $status
     * @param OrderInterface $order
     * @param string|null $comment
     * @return OrderInterface
     */
    public function updateStatus(string $status, OrderInterface $order, ?string $comment = null): ?OrderInterface;

    /**
     * @param OrderInterface $order
     * @param array $data
     * @return OrderInterface|null
     */
    public function saveCurbsideData(OrderInterface $order, array $data): ?OrderInterface;

    /**
     * @param string $token
     * @return null|OrderInterface
     * @throws LocalizedException
     */
    public function getOrderByPickupToken(string $token): ?OrderInterface;

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function clearPickupTokenForOrder(OrderInterface $order): bool;
}
