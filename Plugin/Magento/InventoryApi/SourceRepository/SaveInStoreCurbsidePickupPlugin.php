<?php
/**
 * Curbside pickup for Magento 2
 *
 * This module extends the base pick up in-store Magento 2 functionality
 * adding an option for a curbside pick up
 *
 * @package ImaginationMedia\CurbsidePickup
 * @author Denis Colli Spalenza <denis@imaginationmedia.com>
 * @copyright Copyright (c) 2020 Imagination Media (https://www.imaginationmedia.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

declare(strict_types=1);

namespace ImaginationMedia\CurbsidePickup\Plugin\Magento\InventoryApi\SourceRepository;

use Magento\Framework\DataObject;
use Magento\InventoryApi\Api\Data\SourceExtensionInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\InventoryInStorePickupApi\Api\Data\PickupLocationInterface as Location;

/**
 * Set data to Source itself from its extension attributes to save these values to `inventory_source` DB table.
 */
class SaveInStoreCurbsidePickupPlugin
{
    public const IS_CURBSIDE_PICKUP_LOCATION_ACTIVE = 'is_curbside_pickup_location_active';

    /**
     * Persist the In-Store curbside pickup attribute on Source save
     *
     * @param SourceRepositoryInterface $subject
     * @param SourceInterface $source
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        SourceRepositoryInterface $subject,
        SourceInterface $source
    ): array {
        if (!$source instanceof DataObject) {
            return [$source];
        }

        $extensionAttributes = $source->getExtensionAttributes();

        if ($extensionAttributes !== null) {
            $source->setData(self::IS_CURBSIDE_PICKUP_LOCATION_ACTIVE, $extensionAttributes->getIsCurbsidePickupLocationActive());
        }

        return [$source];
    }
}
