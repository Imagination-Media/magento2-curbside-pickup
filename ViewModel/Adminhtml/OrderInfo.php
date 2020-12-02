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

use Magento\Backend\Block\Template;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Serialize\SerializerInterface;
use ImaginationMedia\CurbsidePickup\Model\Config\Status\Colors;

class OrderInfo implements ArgumentInterface
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var OrderRepositoryInterface
     */
    private $order;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var Http
     */
    private $httpRequest;

    /**
     * @var OrderInterface
     */
    private $currentOrder;

    /**
     * @var SerializerInterface
     */
    private $json;

    /**
     * @var Colors
     */
    private $colorsConfig;

    /**
     * OrderInfo constructor.
     * @param SerializerInterface $json
     * @param Http $httpRequest
     * @param LayoutFactory $layoutFactory
     * @param RequestInterface $request
     * @param OrderRepositoryInterface $order
     * @param Registry $registry
     * @param Colors $colorsConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        SerializerInterface $json,
        Http $httpRequest,
        LayoutFactory $layoutFactory,
        RequestInterface $request,
        OrderRepositoryInterface $order,
        Registry $registry,
        Colors $colorsConfig,
        LoggerInterface $logger
    ) {
        $this->registry = $registry;
        $this->request = $request;
        $this->order = $order;
        $this->logger = $logger;
        $this->layoutFactory = $layoutFactory;
        $this->httpRequest = $httpRequest;
        $this->json = $json;
        $this->colorsConfig = $colorsConfig;
    }

    /**
     * @return OrderInterface|null
     */
    public function getOrder(): ?OrderInterface
    {
        if (!$this->isCurbsideOrderViewPage()) {
            return null;
        }

        try {
            $this->currentOrder = $this->registry->registry('current_order');
            if ($this->currentOrder !== null) {
                return $this->currentOrder;
            }
            if ($this->request->getParam('id')) {
                return null;
            }

            return $this->order->get($this->request->getParam('order_id'));
        } catch (\Exception $e) {
            $this->logger->error($e->getTraceAsString());
        }
    }

    /**
     * @return string
     */
    public function getAdditionalCurbsideOrderHtml(): string
    {
        try {
            $status = $this->getOrder()->getStatus();
            return $this->layoutFactory->create()
                ->createBlock(Template::class)
                ->setTemplate('ImaginationMedia_CurbsidePickup::order/info.phtml')
                ->setStatusColorClass($this->colorsConfig->getStatusColorClass($status))
                ->setOrder($this->getCurrentOrder())
                ->toHtml();

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return '';
        }
    }

    /**
     * @return bool
     */
    public function isCurbsideOrderViewPage(): bool
    {
        return $this->httpRequest->getFullActionName() === 'curbside_order_view';
    }

    /**
     * @return OrderInterface|null
     */
    private function getCurrentOrder(): ?OrderInterface
    {
        return $this->currentOrder ?? null;
    }

    /**
     * @return bool|string
     */
    public function getOrderInfoJson()
    {
        return $this->json->serialize([
            'orderInfo' => $this->getAdditionalCurbsideOrderHtml()
        ]);
    }
}

