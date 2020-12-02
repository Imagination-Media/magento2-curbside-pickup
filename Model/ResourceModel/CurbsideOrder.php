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

namespace ImaginationMedia\CurbsidePickup\Model\ResourceModel;

use ImaginationMedia\CurbsidePickup\Api\Data\TokenExistsInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order;

class CurbsideOrder extends Order implements TokenExistsInterface
{
    /**
     * @param string $token
     * @return bool
     * @throws LocalizedException
     */
    public function doesPickUpTokenExists(string $token): bool
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), 'entity_id')
            ->where('curbside_pickup_token = ?', $token);

        return (bool)$connection->fetchOne($select);
    }

    /**
     * @param int $orderId
     * @param string|null $token
     * @return int
     * @throws LocalizedException
     */
    public function persistToken(int $orderId, ?string $token = null): int
    {
        $connection = $this->getConnection();
        return $connection->update(
            $this->getMainTable(),
            ['curbside_pickup_token' => $token],
            $connection->quoteInto('entity_id = ?', $orderId)
        );
    }
}
