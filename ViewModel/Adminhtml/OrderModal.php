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

namespace ImaginationMedia\CurbsidePickup\ViewModel\Adminhtml;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;

class OrderModal implements ArgumentInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var PricingHelper
     */
    private $pricing;

    /**
     * OrderModal constructor.
     * @param PricingHelper $pricing
     * @param Registry $registry
     */
    public function __construct(PricingHelper $pricing, Registry $registry)
    {
        $this->registry = $registry;
        $this->pricing = $pricing;
    }

    /**
     * @return OrderInterface|null
     */
    public function getOrder(): ?OrderInterface
    {
        return $this->registry->registry('order') ?? null;
    }

    /**
     * @return string
     */
    public function getAmountPaid(): string
    {
        return $this->pricing->currency($this->getOrder()->getTotalPaid(), true, false);
    }
}

