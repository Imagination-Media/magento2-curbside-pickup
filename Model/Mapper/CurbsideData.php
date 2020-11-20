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

namespace ImaginationMedia\CurbsidePickup\Model\Mapper;

use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Serialize\SerializerInterface;

class CurbsideData
{
    /**
     * @var OrderInterface
     */
    private OrderInterface $order;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $json;

    /**
     * CurbsideData constructor.
     * @param SerializerInterface $json
     * @param OrderInterface $order
     */
    public function __construct(SerializerInterface $json, OrderInterface $order)
    {
        $this->order = $order;
        $this->json = $json;
    }

    /**
     * @return string|null
     */
    public function getCarPlate(): ?string
    {
        return $this->getData()->getCarPlate();
    }

    /**
     * @return string|null
     */
    public function getCarColor(): ?string
    {
        return $this->getData()->getCarColor();
    }

    /**
     * @return string|null
     */
    public function getCarModel(): ?string
    {
        return $this->getData()->getCarModel();
    }

    /**
     * @return DataObject
     */
    public function getData(): DataObject
    {
        return new DataObject($this->toArray());
    }

    public function toArray(): array
    {
        $data  = $this->getCurbsideData();
        return $data ?  $this->json->unserialize($data)  : [];
    }

    /**
     * @return OrderInterface
     */
    private function getOrder(): OrderInterface
    {
        return $this->order;
    }

    /**
     * @return string|null
     */
    private function getCurbsideData(): ?string
    {
        return $this->getOrder()->getCurbsideData();
    }
}
