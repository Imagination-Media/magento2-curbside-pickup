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

namespace ImaginationMedia\CurbsidePickup\Model\Config\Source;

use ImaginationMedia\CurbsidePickup\Setup\Patch\Data\OrderStatus;
use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $result = [];
        foreach ($this->getOptions() as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getOptions(): array
    {
        return [
            OrderStatus::STATUS_ACCEPTED => __(OrderStatus::STATUS_ACCEPTED_LABEL),
            OrderStatus::STATUS_READY_TO_PICK_UP => __(OrderStatus::STATUS_READY_TO_PICK_UP_LABEL),
            OrderStatus::STATUS_CUSTOMER_READY => __(OrderStatus::STATUS_CUSTOMER_READY_LABEL)
        ];
    }

    /**
     * @return array
     */
    public function getGridStatusOptions(): array
    {
        return [
            OrderStatus::STATUS_ACCEPTED => __(OrderStatus::STATUS_ACCEPTED_GRID_ACTION_LABEL),
            OrderStatus::STATUS_READY_TO_PICK_UP => __(OrderStatus::STATUS_READY_TO_PICK_UP_GRID_ACTION_LABEL),
            OrderStatus::STATUS_CUSTOMER_READY => __(OrderStatus::STATUS_CUSTOMER_READY_GRID_ACTION_LABEL)
        ];
    }
}
