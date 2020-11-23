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

use ImaginationMedia\CurbsidePickup\Model\Config\Source\Status;
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
     * @var Status
     */
    private $status;

    /**
     * StatusActions constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param LayoutInterface $layout
     * @param Status $status
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        LayoutInterface $layout,
        Status $status,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->layout = $layout;
        $this->status = $status;

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
                    $item[$this->getData('name')] = $this->layout->createBlock(
                        Button::class,
                        'order_status_action_btn_' . $item['entity_id'],
                        [
                            'data' => [
                                'label' => __($this->getLabelByStatus($item['status'])),
                                'type' => 'button',
                            //    'disabled' => $this->isActionEnabled($item['status']),
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
        } elseif ($status === OrderStatus::STATUS_READY_TO_PICK_UP) {
            return $this->getDeliverActionUrl();
        }
        return $this->getAcceptActionUrl();
    }

    /**
     * @param string $status
     * @return string
     */
    private function getLabelByStatus(string $status): string
    {
        if ($status === Order::STATE_COMPLETE) {
            return OrderStatus::STATUS_COMPLETE_LABEL;
        }

        $options = $this->getGridStatusOptions();
        return isset($options[$status]) ? $options[$status] : OrderStatus::STATUS_ACCEPTED_GRID_ACTION_LABEL;
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
        $curbsideStatuses = array_keys($this->getStatusOptions());
        return $status === Order::STATE_COMPLETE || !in_array($status, $curbsideStatuses);
    }

    /**
     * @return array
     */
    private function getGridStatusOptions(): array
    {
        return $this->status->getGridStatusOptions();
    }

    /**
     * @return array
     */
    private function getStatusOptions(): array
    {
        return $this->status->getOptions();
    }
}
