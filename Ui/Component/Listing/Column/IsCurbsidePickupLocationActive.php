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

namespace ImaginationMedia\CurbsidePickup\Ui\Component\Listing\Column;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Render curbside pickup location on sources grid.
 */
class IsCurbsidePickupLocationActive extends Column
{
    public const IS_CURBSIDE_PICKUP_LOCATION_ACTIVE = 'is_curbside_pickup_location_active';

    /**
     * Move extension attribute value to row data.
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource):array
    {
        if (!isset($dataSource['data']['totalRecords'])) {
            return $dataSource;
        }

        if ((int)$dataSource['data']['totalRecords'] === 0) {
            return $dataSource;
        }

        return $this->normalizeData($dataSource);
    }

    /**
     * Normalize source data.
     *
     * @param array $dataSource
     * @return array
     */
    private function normalizeData(array $dataSource):array
    {
        foreach ($dataSource['data']['items'] as &$row) {
            $row[self::IS_CURBSIDE_PICKUP_LOCATION_ACTIVE] =
                $row[ExtensibleDataInterface::EXTENSION_ATTRIBUTES_KEY]
                [self::IS_CURBSIDE_PICKUP_LOCATION_ACTIVE] ?? '';
        }

        return $dataSource;
    }
}
