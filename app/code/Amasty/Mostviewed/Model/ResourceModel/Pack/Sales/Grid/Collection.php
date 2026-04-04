<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Pack\Sales\Grid;

use Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\PackSales\Table;
use Amasty\Mostviewed\Model\ResourceModel\Pack\Sale;
use Amasty\Mostviewed\Ui\Component\PackSale\DataProvider\Document;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    private $aggregations;

    public function _construct()
    {
        $this->_init(Document::class, Sale::class);
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
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
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
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $orderGridTable = $this->getConnection()->select()->from(
            $this->getTable('sales_order_grid'),
            ['entity_id', 'created_at', 'status', 'base_currency_code', 'order_currency_code', 'increment_id']
        );
        $this->getSelect()->join(
            ['sales_order' => $orderGridTable],
            sprintf('main_table.%s = sales_order.entity_id', Table::ORDER_ID_COLUMN)
        );

        parent::_renderFiltersBefore();
    }
}
