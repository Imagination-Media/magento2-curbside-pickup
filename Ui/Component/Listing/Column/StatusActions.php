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

namespace ImaginationMedia\CurbsidePickup\Ui\Component\Listing\Column;

use ImaginationMedia\CurbsidePickup\Setup\Patch\Data\OrderStatus;
use Magento\Backend\Block\Widget\Button;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Model\Order;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Render Order Status Buttons on Order Grid
 */
class StatusActions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * StatusActions constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param LayoutInterface $layout
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        LayoutInterface $layout,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->layout = $layout;

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id']) && isset($item['status'])) {
                    $statusLabel = $this->getLabelByStatus($item['status']);
                    if ($statusLabel === null) {
                        continue;
                    }
                    $item[$this->getData('name')] = $this->layout->createBlock(
                        Button::class,
                        'order_status_action_btn_' . $item['entity_id'],
                        [
                            'data' => [
                                'label' => __($statusLabel),
                                'type' => 'button',
                                'disabled' => $this->isActionEnabled($item['status']),
                                'id' => 'order-' .  $item['entity_id'],
                                'class' => 'order-status-action',
                                'onclick' => 'curbsidePickup.triggerOrderStatusChange(
                                    \'' . $this->getNextActionUrl($item['status']) . '\',
                                    \'' . $item['entity_id'] . '\'
                                )'
                            ]
                        ]
                    )->toHtml();
                }
            }
        }

        return $dataSource;
    }

    /**
     * @param string $status
     * @return string
     */
    private function getNextActionUrl(string $status): string
    {
        if ($status === OrderStatus::STATUS_ACCEPTED) {
            return $this->getReadyActionUrl();
        } elseif ($status === OrderStatus::STATUS_CUSTOMER_READY) {
            return $this->getDeliverActionUrl();
        }
        return $this->getAcceptActionUrl();
    }

    /**
     * @param string $status
     * @return string|null
     */
    private function getLabelByStatus(string $status): ?string
    {
        if ($status === OrderStatus::STATUS_ACCEPTED) {
            return  OrderStatus::STATUS_READY_TO_PICK_UP_GRID_ACTION_LABEL;
        } elseif ($status === OrderStatus::STATUS_READY_TO_PICK_UP) {
            return  OrderStatus::STATUS_WAITING_USER_LABEL;
        } elseif ($status === OrderStatus::STATUS_CUSTOMER_READY) {
            return  OrderStatus::STATUS_CUSTOMER_READY_GRID_ACTION_LABEL;
        } elseif ($status === Order::STATE_COMPLETE) {
            return  OrderStatus::STATUS_COMPLETE_LABEL;
        } elseif (in_array($status, [Order::STATE_NEW, OrderStatus::STATUS_PENDING])) {
            return OrderStatus::STATUS_ACCEPTED_GRID_ACTION_LABEL;
        }
        return null;
    }

    /**
     * @return string
     */
    private function getAcceptActionUrl(): string
    {
        return $this->urlBuilder->getUrl(
            $this->getData('config/acceptOrderUrlPath')
        );
    }

    /**
     * @return string
     */
    private function getReadyActionUrl(): string
    {
        return $this->urlBuilder->getUrl(
            $this->getData('config/readyOrderUrlPath')
        );
    }

    /**
     * @return string
     */
    private function getDeliverActionUrl(): string
    {
        return $this->urlBuilder->getUrl(
            $this->getData('config/deliverOrderUrlPath')
        );
    }

    /**
     * @param string $status
     * @return bool
     */
    private function isActionEnabled(string $status): bool
    {
        return in_array($status, [
           Order::STATE_NEW,
           OrderStatus::STATUS_ACCEPTED,
           OrderStatus::STATUS_CUSTOMER_READY,
           OrderStatus::STATUS_PENDING
        ]);
    }
}
