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

namespace ImaginationMedia\CurbsidePickup\ViewModel;

use ImaginationMedia\CurbsidePickup\Model\Mapper\CurbsideDataFactory;
use ImaginationMedia\CurbsidePickup\Setup\Patch\Data\OrderStatus;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use ImaginationMedia\CurbsidePickup\Helper\Data;
use Magento\Sales\Model\Order;

class OrderPickup implements ArgumentInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $json;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var CurbsideDataFactory
     */
    private CurbsideDataFactory $curbsideDataFactory;

    /**
     * @var Data
     */
    private Data $helper;

    /**
     * OrderPickup constructor.
     * @param CurbsideDataFactory $curbsideDataFactory
     * @param SerializerInterface $json
     * @param RequestInterface $request
     * @param OrderRepositoryInterface $orderRepository
     * @param Data $helper
     * @param Context $context
     */
    public function __construct(
        CurbsideDataFactory $curbsideDataFactory,
        SerializerInterface $json,
        RequestInterface $request,
        OrderRepositoryInterface $orderRepository,
        Data $helper,
        Context $context
    ) {
        $this->context = $context;
        $this->orderRepository = $orderRepository;
        $this->json = $json;
        $this->request = $request;
        $this->curbsideDataFactory = $curbsideDataFactory;
        $this->helper = $helper;
    }

    /**
     * @return DataObject
     */
    public function getCurbsideData(): DataObject
    {
        return $this->curbsideDataFactory->create(['order' => $this->getOrder()])->getData();
    }

    /**
     * @return string|null
     */
    public function getOrderId(): ?string
    {
        if (!$this->getOrder()) {
            return null;
        }
        return $this->getOrder()->getEntityId();
    }

    /**
     * @return string
     */
    public function getPickupScheduledTime(): string
    {
        $deliveryTime = new \DateTime($this->getOrder()->getCurbsideDeliveryTime());
        return $deliveryTime->format('m/d/y h:i A');
    }

    /**
     * @return bool
     */
    public function isCustomerReadyToPickUp(): bool
    {
        return $this->getOrder()->getStatus() === OrderStatus::STATUS_CUSTOMER_READY;
    }

    /**
     * @return bool
     */
    public function isOrderReadyToPickUp(): bool
    {
        return $this->getOrder()->getStatus() === OrderStatus::STATUS_READY_TO_PICK_UP
            && strtotime('now') > strtotime($this->getOrder()->getCurbsideDeliveryTime());
    }

    /**
     * @return string|null
     */
    public function getPickupButtonTitle(): ?string
    {
        if ($this->isOrderReadyToPickUp() && !$this->isScheduledPickupActive()) {
            return 'I\'m here';
        } elseif ($this->isScheduledPickupActive()) {
            return 'Schedule Pick Up';
        }
        return 'Save';
    }

    /**
     * @return bool
     */
    public function isOrderPickedUp(): bool
    {
        return $this->getOrder()->getStatus() === Order::STATE_COMPLETE
            && $this->getOrder()->getCurbside();
    }

    /**
     * @return string
     */
    public function getSoonestDeliveryTime(): string
    {
        $store = $this->getOrder()->getStoreId();
        $deliveryThreshold = $this->helper->getPickupThreshold($store);

        $deliveryDate = new \DateTime('now');
        if ($deliveryThreshold) {
            $thresholdTimeSpan = new \DateInterval('PT' . $deliveryThreshold . 'H');
            $deliveryDate = $deliveryDate->add($thresholdTimeSpan);
        }
        return $deliveryDate->format('m/d/y h:i');
    }

    /**
     * @return string|null
     */
    public function getStore(): ?string
    {
        if (!$this->getOrder()) {
            return null;
        }
        return $this->getOrder()->getStoreId();
    }

    /**
     * @return bool
     */
    public function isScheduledPickupActive(): bool
    {
        return $this->helper->isScheduledPickupEnabled();
    }

    /**
     * @return OrderInterface|null
     */
    private function getOrder():  ?OrderInterface
    {
        $orderId = $this->request->getParam('order_id');
        return $this->orderRepository->get($orderId);
    }
}
