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

namespace ImaginationMedia\CurbsidePickup\Ui\Component\Listing\Column;

use Magento\Backend\Block\Widget\Button;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Render Quick View Button on Order Grid
 */
class ModalViewAction extends Column
{
    private const VIEW_ACTION_LABEL = 'View Order';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * ModalViewAction constructor.
     *
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
     * @return string
     */
    public function getModalViewUrl(): string
    {
        return $this->urlBuilder->getUrl(
            $this->getData('config/modalViewUrlPath')
        );
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['entity_id'])) {
                    $item[$this->getData('name')] = $this->layout->createBlock(
                        Button::class,
                        'order_details_modal_btn_' . $item['entity_id'],
                        [
                            'data' => [
                                'label' => __(self::VIEW_ACTION_LABEL),
                                'type' => 'button',
                                'disabled' => false,
                                'class' => 'order-details-modal',
                                'onclick' => 'curbsidePickup.openDetailsInModal(
                                    \'' . $this->getModalViewUrl() . '\',
                                    \'' . $item['entity_id'] .'\'
                                )'
                            ]
                        ]
                    )->toHtml();
                }
            }
        }

        return $dataSource;
    }
}
