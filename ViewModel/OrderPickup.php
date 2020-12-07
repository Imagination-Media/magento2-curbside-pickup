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

use ImaginationMedia\CurbsidePickup\Action\CurbsideOrderInterface;
use ImaginationMedia\CurbsidePickup\Model\Mapper\CurbsideData;
use ImaginationMedia\CurbsidePickup\Model\Mapper\CurbsideDataFactory;
use ImaginationMedia\CurbsidePickup\Setup\Patch\Data\OrderStatus;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\RequestInterface;
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
     * @var CurbsideOrderInterface
     */
    private CurbsideOrderInterface $curbsideOrderService;

    private  $token = null;

    /**
     * OrderPickup constructor.
     * @param CurbsideOrderInterface $curbsideOrderService
     * @param CurbsideDataFactory $curbsideDataFactory
     * @param SerializerInterface $json
     * @param RequestInterface $request
     * @param OrderRepositoryInterface $orderRepository
     * @param Data $helper
     * @param Context $context
     */
    public function __construct(
        CurbsideOrderInterface $curbsideOrderService,
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
        $this->curbsideOrderService = $curbsideOrderService;
    }

    /**
     * @return CurbsideData
     */
    public function getCurbsideData(): CurbsideData
    {
        return $this->curbsideDataFactory->create(['order' => $this->getOrder()]);
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
     * @return string|null
     */
    public function getPickupScheduledTime(): ?string
    {
        if (!$this->getOrder()->getCurbsideDeliveryTime()) {
            return null;
        }

        return $this->helper->displayInCurrentTimezone(
            $this->getOrder()->getCurbsideDeliveryTime(),
            'm/d/y h:i A'
        );
    }

    /**
     * @return bool
     */
    public function isCustomerReadyToPickUp(): bool
    {
        return $this->getOrder()->getStatus() === OrderStatus::STATUS_CUSTOMER_READY;
    }

    /**
     * @return string|null
     */
    public function getPickupButtonTitle(): ?string
    {
       if ($this->isScheduledPickupActive() && !$this->isOrderScheduledForPickup()) {
            return 'Schedule Pick Up';
        }
        return 'I\'m on Location ready';
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
        return (bool)$this->helper->isScheduledPickupEnabled();
    }

    /**
     * @return bool
     */
    public function isOrderScheduledForPickup(): bool
    {
        return $this->getCurbsideData()->getScheduledPickup() === 'Yes';
    }

    /**
     * @return OrderInterface|null
     */
    private function getOrder():  ?OrderInterface
    {
        $orderId = $this->request->getParam('order_id');
        if ($orderId === null) {
            $token = $this->getPickupAccessToken();
            return $this->curbsideOrderService->getOrderByPickupToken($token);
        }

        return $this->orderRepository->get($orderId);
    }

    /**
     * @return string
     */
    public function getPickupAccessTokenQueryString(): string
    {
        $token = $this->getPickupAccessToken();
        return $token ? '?token=' . $token : '';
    }

    /**
     * @return string|null
     */
    public function getPickupAccessToken(): ?string
    {
        if ($this->token === null) {
            $this->token = $this->request->getParam('token');
        }
        return $this->token;
    }
}
