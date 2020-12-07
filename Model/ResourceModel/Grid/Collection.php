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

namespace ImaginationMedia\CurbsidePickup\Model\ResourceModel\Grid;

use ImaginationMedia\CurbsidePickup\Setup\Patch\Data\OrderStatus;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;

class Collection extends SearchResult
{
    private const CURBSIDE_FIELD = 'curbside';

    private const CURBSIDE_DELIVERY_TIME_FIELD = 'curbside_delivery_time';

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * Collection constructor.
     * @param RequestInterface $request
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param $mainTable
     * @param null $resourceModel
     * @param null $identifierName
     * @param null $connectionName
     * @throws LocalizedException
     */
    public function __construct(
        RequestInterface $request,
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable,
        $resourceModel = null,
        $identifierName = null,
        $connectionName = null
    ) {
        $this->request = $request;

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel,
            $identifierName,
            $connectionName
        );
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $data = $this->request->getParams();
        $this->addFieldToFilter(self::CURBSIDE_FIELD, OrderStatus::STATUS_ACTIVE);
        if (empty($data['sorting'])) {
           $this->setOrder(self::CURBSIDE_DELIVERY_TIME_FIELD, 'ASC');
        }

        return $this;
    }

    /**
     * Load Curbside Order Data To Grid
     */
    protected function _renderFiltersBefore()
    {
        $joinTable = $this->getTable('sales_order');
        $this->getSelect()->joinLeft(
            ['sales_order_table' => $joinTable],
            'main_table.entity_id = sales_order_table.entity_id',
            ['curbside', 'curbside_data', 'curbside_delivery_time']
        );
        parent::_renderFiltersBefore();
    }

    /**
     * Add field to filter.
     *
     * @param string|array $field
     * @param string|int|array|null $condition
     * @return SearchResult
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field && isset($condition['like']) && in_array($field, ['curbside_delivery_time'])) {
            $condition['like'] = str_replace(' ', '%', (string)$condition['like']);
        }
        return parent::addFieldToFilter($field, $condition);
    }
}
