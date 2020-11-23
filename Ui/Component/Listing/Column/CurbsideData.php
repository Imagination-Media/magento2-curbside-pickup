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

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\LayoutInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Render Curbside JSON Data as Formatted List
 */
class CurbsideData extends Column
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
     * @var SerializerInterface
     */
    private $json;

    /**
     * CurbsideData constructor.
     *
     * @param ContextInterface $context
     * @param SerializerInterface $json
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        SerializerInterface $json,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;

        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->json = $json;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (!isset($item['curbside_data']) || $item['curbside_data'] === null) {
                    continue;
                }
                $curbsideData = $this->json->unserialize($item['curbside_data']);
                $item[$this->getData('name')] = $this->formatToList($curbsideData);
            }
        }

        return $dataSource;
    }

    /**
     * @param array $curbsideData
     * @return string
     */
    private function formatToList(array $curbsideData): string
    {
        if (empty($curbsideData)) {
            return '';
        }

        $curbsideDataList = '<ul>';
        foreach ($curbsideData as $field => $item) {
            if (!$item) {
                continue;
            }
            if (is_array($item) && !empty($item)) {
                $curbsideDataList .= '<li>';
                $this->formatToList($item);
                $curbsideDataList .= '</li>';
            } else {
                $curbsideDataList .= '<li>' . $this->formatToLabel($field) . ': ' . $item . '</li>';
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
