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

namespace ImaginationMedia\CurbsidePickup\ViewModel;

use ImaginationMedia\CurbsidePickup\Setup\Patch\Data\OrderStatus;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use ImaginationMedia\CurbsidePickup\Model\Config\Source\Status;

class OrderHistory implements ArgumentInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var Status
     */
    private $curbsideStatuses;

    /**
     * OrderHistory constructor.
     * @param UrlInterface $url
     * @param Status $curbsideStatuses
     * @param LoggerInterface $logger
     */
    public function __construct(
        UrlInterface $url,
        Status $curbsideStatuses,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->url = $url;
        $this->curbsideStatuses = $curbsideStatuses;
    }

    /**
     * @param bool|OrderCollection $collection
     * @return OrderCollection|bool
     */
    public function filterCurbsideOrders($collection)
    {
        return $collection;
        if (!$collection) {
            return $collection;
        }
        try {
            return $collection->addFieldToFilter(['curbside', 'status'], [
                     ['eq' => OrderStatus::STATUS_ACTIVE],
                     ['in' => [$this->getCurbsideStatusOptions()]]
                ]
            );
        } catch (\Exception $e) {
            $this->logger->error($e->getTraceAsString());
        }
    }

    /**
     * @param OrderInterface $order
     * @return string
     */
    public function getViewPageUrl(OrderInterface $order): string
    {
        return $this->url->getUrl('curbside/order/view', ['order_id' => $order->getId()]);
    }

    /**
     * @return array
     */
    private function getCurbsideStatusOptions(): array
    {
        return array_keys($this->curbsideStatuses->getOptions());
    }
}

