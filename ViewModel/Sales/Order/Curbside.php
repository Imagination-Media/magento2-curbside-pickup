<?php

/**
 * Curbside pickup for Magento 2
 *
 * This module extends the base pick up in-store Magento 2 functionality
 * adding an option for a curbside pick up
 *
 * @package ImaginationMedia\CurbsidePickup
 * @author Igor Ludgero Miura <igor@imaginationmedia.com>
 * @copyright Copyright (c) 2020 Imagination Media (https://www.imaginationmedia.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

declare(strict_types=1);

namespace ImaginationMedia\CurbsidePickup\ViewModel\Sales\Order;

use ImaginationMedia\CurbsidePickup\Setup\Patch\Data\OrderStatus;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

/**
 * Class Curbside
 * @package ImaginationMedia\CurbsidePickup\ViewModel\Sales\Order
 */
class Curbside implements ArgumentInterface
{
    /**
     * @var Session
     */
    protected Session $session;

    /**
     * @var OrderCollectionFactory
     */
    protected OrderCollectionFactory $orderCollectionFactory;

    /**
     * Curbside constructor.
     * @param Session $session
     * @param OrderCollectionFactory $orderCollectionFactory
     */
    public function __construct(
        Session $session,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        $this->session = $session;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * Get customer curbside order collection
     *
     * @param int|null $customerId
     * @return OrderCollection
     */
    public function getCurbsideOrder(?int $customerId = null): OrderCollection
    {
        if (!$customerId) {
            $customerId = (int)$this->session->getCustomerId();
        }

        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter("customer_id", ["eq" => $customerId])
            ->addFieldToFilter(OrderStatus::FIELD_CURBSIDE, ["eq" => true])
            ->addFieldToSelect([
                "increment_id",
                "created_at",
                OrderStatus::FIELD_CURBSIDE_DELIVERY_TIME,
                "status",
                "grand_total",
                OrderStatus::FIELD_CURBSIDE_DATA
            ]);
        return $collection;
    }
}
