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

namespace ImaginationMedia\CurbsidePickup\Plugin\Magento\InventoryInStorePickup\Model\Source;

use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\DataObject;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryInStorePickupApi\Api\Data\PickupLocationInterface;

class InitPickupLocationExtensionAttributes
{
    public const IS_CURBSIDE_PICKUP_LOCATION_ACTIVE = 'is_curbside_pickup_location_active';

    /**
     * @var ExtensionAttributesFactory
     */
    private $extensionAttributesFactory;

    /**
     * @param ExtensionAttributesFactory $extensionAttributesFactory
     */
    public function __construct(ExtensionAttributesFactory $extensionAttributesFactory)
    {
        $this->extensionAttributesFactory = $extensionAttributesFactory;
    }

    /**
     * @param \Magento\InventoryInStorePickup\Model\Source\InitPickupLocationExtensionAttributes $subject
     * @param \Closure $proceed
     * @param $source
     */
    public function aroundExecute(
        \Magento\InventoryInStorePickup\Model\Source\InitPickupLocationExtensionAttributes $subject,
        \Closure $proceed,
        SourceInterface $source
    ) : void {
        if (!$source instanceof DataObject) {
            return;
        }

        $pickupAvailable = $source->getData(PickupLocationInterface::IS_PICKUP_LOCATION_ACTIVE);
        $curbsidePickupAvailable = $source->getData(self::IS_CURBSIDE_PICKUP_LOCATION_ACTIVE);
        $frontendName = $source->getData(PickupLocationInterface::FRONTEND_NAME);
        $frontendDescription = $source->getData(PickupLocationInterface::FRONTEND_DESCRIPTION);

        $extensionAttributes = $source->getExtensionAttributes();

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->extensionAttributesFactory->create(SourceInterface::class);
            /** @noinspection PhpParamsInspection */
            $source->setExtensionAttributes($extensionAttributes);
        }

        $extensionAttributes
            ->setIsPickupLocationActive((bool)$pickupAvailable)
            ->setIsCurbsidePickupLocationActive((bool)$curbsidePickupAvailable)
            ->setFrontendName($frontendName)
            ->setFrontendDescription($frontendDescription);
    }
}

