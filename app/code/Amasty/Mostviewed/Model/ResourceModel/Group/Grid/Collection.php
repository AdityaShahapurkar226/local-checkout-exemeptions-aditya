<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Group\Grid;

use Amasty\Mostviewed\Model\Group;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Amasty\Mostviewed\Model\ResourceModel\Group\Collection as GroupCollection;
use Amasty\Mostviewed\Api\Data\AnalyticInterface;
use Amasty\Mostviewed\Plugin\Sales\Model\Service\OrderService;

class Collection extends GroupCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    private $aggregations;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * {@inheritdoc}
     */
    public function _renderFiltersBefore()
    {
        $this->getSelect()->joinLeft(
            ['statistics_1' => $this->getTable(AnalyticInterface::MAIN_TABLE)],
            'main_table.group_id = statistics_1.block_id AND statistics_1.type = "view"',
            [
                'impression' => new \Zend_Db_Expr('IF(statistics_1.counter, statistics_1.counter, 0)')
            ]
        )->joinLeft(
            ['statistics_2' => $this->getTable(AnalyticInterface::MAIN_TABLE)],
            'main_table.group_id = statistics_2.block_id AND statistics_2.type = "click_block"',
            [
                'click_block' => new \Zend_Db_Expr('IF(statistics_2.counter, statistics_2.counter, 0)')
            ]
        )->joinLeft(
            ['statistics_3' => $this->getTable(AnalyticInterface::MAIN_TABLE)],
            'main_table.group_id = statistics_3.block_id AND statistics_3.type = "click_cart"',
            [
                'click_cart' => new \Zend_Db_Expr('IF(statistics_3.counter, statistics_3.counter, 0)'),
                'click' => new \Zend_Db_Expr(
                    '(IF(statistics_2.counter, statistics_2.counter, 0)'
                            . '+ IF(statistics_3.counter, statistics_3.counter, 0))'
                )
            ]
        )->joinLeft(
            ['statistics_4' => $this->getTable(AnalyticInterface::MAIN_TABLE)],
            'main_table.group_id = statistics_4.block_id AND statistics_4.type = "' . OrderService::ORDERS_MADE . '"',
            [
                OrderService::ORDERS_MADE => new \Zend_Db_Expr('IF(statistics_4.counter, statistics_4.counter, 0)')
            ]
        )->joinLeft(
            ['statistics_5' => $this->getTable(AnalyticInterface::MAIN_TABLE)],
            'main_table.group_id = statistics_5.block_id AND statistics_5.type = "' . OrderService::REVENUE . '"',
            [
                OrderService::REVENUE => new \Zend_Db_Expr('IF(statistics_5.counter, statistics_5.counter, 0)')
            ]
        );

        parent::_renderFiltersBefore();
    }
}
