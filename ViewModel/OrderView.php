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

use Magento\Framework\App\Http\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

class OrderView implements ArgumentInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var UrlInterface
     */
    private UrlInterface $url;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $json;

    /**
     * OrderView constructor.
     * @param SerializerInterface $json
     * @param UrlInterface $url
     * @param RequestInterface $request
     * @param OrderRepositoryInterface $orderRepository
     * @param Registry $registry
     * @param LoggerInterface $logger
     * @param Context $context
     */
    public function __construct(
        SerializerInterface $json,
        UrlInterface $url,
        RequestInterface $request,
        OrderRepositoryInterface $orderRepository,
        Registry $registry,
        LoggerInterface $logger,
        Context $context
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->orderRepository = $orderRepository;
        $this->url = $url;
        $this->request = $request;
        $this->logger = $logger;
        $this->json = $json;
    }

    /**
     * Return back title for logged in and guest users
     *
     * @return Phrase
     */
    public function getBackTitle(): Phrase
    {
        if ($this->context->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH)) {
            return __('Back to My Curbside Orders');
        }
        return __('View Another Curbside Order');
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
    public function getPickupViewUrl(): string
    {
        $orderId = $this->request->getParam('order_id');
        return $this->url->getUrl('*/*/pickup', ['order_id' => $orderId]);
    }

    /**
     * @return string
     */
    public function getBackUrl(): string
    {
        return $this->url->getUrl('*/*/view');
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
     * @return OrderInterface|null
     */
    private function getOrder():  ?OrderInterface
    {
        try {
            $order = $this->registry->registry('current_order');
            if ($order === null) {
                $orderId = $this->request->getParam('order_id');
                $order = $this->orderRepository->get($orderId);
            }
            return $order;
        } catch (\Exception $e) {
            $this->logger->error($e->getTraceAsString());
        }
    }

    /**
     * @param string $jsonCurbsideData
     * @return string
     */
    public function formatToHtmlList(?string $jsonCurbsideData): string
    {
        if ($jsonCurbsideData === null) {
            return '-';
        }
        $curbsideData = $this->json->unserialize($jsonCurbsideData);
        $curbsideDataList = '<ul class="curbside_data_list">';
        foreach ($curbsideData as $field => $item) {
            if (!$item) {
                continue;
            }
            if (is_array($item) && !empty($item)) {
                $curbsideDataList .= '<li>';
                $this->formatToHtmlList($this->json->serialize($item));
                $curbsideDataList .= '</li>';
            } else {
                $curbsideDataList .= '<li><span class="curbside_data_label">' . $this->formatToLabel($field) . '</span>: ' . $item . '</li>';
            }
        }
        $curbsideDataList .= "</ul>";

        return $curbsideDataList;
    }

    /**
     * @param string $key
     * @return string
     */
    private function formatToLabel(string $key): string
    {
        return ucfirst(str_replace('_', ' ', $key));
    }
}
