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

namespace ImaginationMedia\CurbsidePickup\Rewrite\Magento\Quote\Api\Data;

class AddressExtension extends \Magento\Quote\Api\Data\AddressExtension
{
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * @return boolean|null
     */
    public function getIsCurbsidePickupLocationActive()
    {
        return $this->_get('is_curbside_pickup_location_active');
    }

    /**
     * @param boolean $isPickupLocationActive
     * @return \Magento\InventoryApi\Api\Data\SourceExtension
     */
    public function setIsCurbsidePickupLocationActive($isPickupLocationActive)
    {
        $this->setData('is_curbside_pickup_location_active', $isPickupLocationActive);
        return $this;
    }
}

