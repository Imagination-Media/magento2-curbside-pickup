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

namespace ImaginationMedia\CurbsidePickup\Plugin\Magento\QuoteApi;

use ImaginationMedia\CurbsidePickup\Helper\Data;
use ImaginationMedia\CurbsidePickup\Rewrite\Magento\Quote\Api\Data\AddressExtension;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address;

class CartRepositoryInterfacePlugin
{
    private const CURBSIDE_ACTIVE = '1';

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $json;

    /**
     * @var SourceRepositoryInterface
     */
    private SourceRepositoryInterface $sourceRepository;

    /**
     * @var Data
     */
    private Data $helper;

    /**
     * CartRepositoryInterfacePlugin constructor.
     * @param SourceRepositoryInterface $sourceRepository
     * @param SerializerInterface $json
     * @param Data $helper
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        SerializerInterface $json,
        Data $helper
    ) {
        $this->json = $json;
        $this->sourceRepository = $sourceRepository;
        $this->helper = $helper;
    }

    /**
     * Fill quote with Curbside Data from Shipping Address.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $repository
     * @param CartInterface $cart
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(CartRepositoryInterface $repository, CartInterface $cart)
    {
        if ($cart) {
            $extension = $cart->getExtensionAttributes();
            $assignments = $extension->getShippingAssignments();
            if (!$assignments) {
                return [$cart];
            }

            /** @var ShippingAssignmentInterface $assignment */
            $assignment = current($assignments);
            $shipping = $assignment->getShipping();
            $address = $shipping->getAddress();
            if ($address instanceof Address) {
                $address->setShippingMethod('');
            }

            /** @var AddressExtension $addressExtensionAttributes */
            $addressExtensionAttributes = $address->getExtensionAttributes();
            if ($addressExtensionAttributes) {
                if ($addressExtensionAttributes->getIsCurbsidePickupLocationActive()) {
                    $cart->setCurbside(self::CURBSIDE_ACTIVE);
                    if ($addressExtensionAttributes->getPickupLocationCode() !== null) {
                        $inventorySourceName = $this->getSourceNameByCode($addressExtensionAttributes->getPickupLocationCode());
                        $cart->setCurbsideData($this->json->serialize(['pickup_location_name' => $inventorySourceName]));
                    }
                    $pickUpThreshold = $this->helper->getPickupThreshold();
                    $deliveryTime = (new \DateTime('now'));
                    if ($pickUpThreshold !== null && (int)$pickUpThreshold > 0) {
                        $pickUpThresholdIntervalInHours = new \DateInterval('PT' . $pickUpThreshold . 'H');
                        $deliveryTime->add($pickUpThresholdIntervalInHours);
                    }
                    $cart->setCurbsideDeliveryTime($deliveryTime->format('Y-m-d H:i:s'));
                }
            }
        }
        return [$cart];
    }

    /**
     * @param string $sourceCode
     * @return string
     * @throws NoSuchEntityException
     */
    private function getSourceNameByCode(string $sourceCode): string
    {
        return $this->sourceRepository->get($sourceCode)->getName();
    }
}
