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
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $json;

    /**
     * @var SourceRepositoryInterface
     */
    private SourceRepositoryInterface $sourceRepository;

    /**
     * CartRepositoryInterfacePlugin constructor.
     * @param SourceRepositoryInterface $sourceRepository
     * @param SerializerInterface $json
     */
    public function __construct(
        SourceRepositoryInterface $sourceRepository,
        SerializerInterface $json
    ) {
        $this->json = $json;
        $this->sourceRepository = $sourceRepository;
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
                $isCurbside = $addressExtensionAttributes->getIsCurbsidePickupLocationActive() ? '1' : '0';
                $cart->setCurbside($isCurbside);
                if ($addressExtensionAttributes->getPickupLocationCode() !== null) {
                    $inventorySourceName = $this->getSourceNameByCode($addressExtensionAttributes->getPickupLocationCode());
                    $cart->setCurbsideData($this->json->serialize(['pickup_location_name' => $inventorySourceName]));
                }
                $cart->setCurbsideDeliveryTime((new \DateTime('now'))->format('Y-m-d H:i:s'));
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
