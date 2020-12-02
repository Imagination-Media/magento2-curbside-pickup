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

namespace ImaginationMedia\CurbsidePickup\ViewModel\Adminhtml;

use ImaginationMedia\CurbsidePickup\Model\Config\Status\Colors;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class OrderStatusColors implements ArgumentInterface
{
    /**
     * @var Colors
     */
    private $colorsConfig;

    /**
     * OrderStatusColors constructor.
     * @param Colors $colorsConfig
     */
    public function __construct(Colors $colorsConfig)
    {
        $this->colorsConfig = $colorsConfig;
    }

    /**
     * @return Colors
     */
    public function getGridColorsConfiguration(): Colors
    {
        return $this->colorsConfig;
   }

    /**
     * @param string $status
     * @return string
     */
    public function getStatusColorClass(string $status): string
    {
        return $this->getGridColorsConfiguration()->getStatusColorClass($status);
    }
}

