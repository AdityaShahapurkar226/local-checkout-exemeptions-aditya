<?php

namespace Ewave\ExtendedBundleProduct\Model\ResourceModel\Selection;

use Ewave\ExtendedBundleProduct\Model\Product as ExtendedBundleProduct;
use Magento\CatalogInventory\Model\Stock;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class Collection
 *
 * @package Ewave\ExtendedBundleProduct\Model\ResourceModel\Selection
 */
class Collection extends \Magento\Bundle\Model\ResourceModel\Selection\Collection
{
    /**
     * @var ExtendedBundleProduct
     */
    protected $extendedBundleType;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @return ExtendedBundleProduct
     */
    protected function getExtendedBundleType()
    {
        if ($this->extendedBundleType === null) {
            $this->extendedBundleType = $this->_entityFactory->create(ExtendedBundleProduct::class);
        }

        return $this->extendedBundleType;
    }

    /**
     * Add filtering of product then havent enoght stock
     *
     * @return $this
     */
    public function addQuantityFilter()
    {
        $extendedTypes = $this->getExtendedBundleType()->getNonCompositeProductTypes();
        $manageStockExpr = $this->getManageStockExpr('stock_item');
        $backOrdersExpr = $this->getBackordersExpr('stock_item');
        $minQtyExpr = $this->getConnection()->getCheckSql(
            'selection.selection_can_change_qty',
            $this->getMinSaleQtyExpr('stock_item'),
            'selection.selection_qty'
        );

        $where = $manageStockExpr . ' = 0';
        $where .= ' OR ('
            . 'stock_item.is_in_stock = ' . Stock::STOCK_IN_STOCK
            . ' AND ('
            . $backOrdersExpr . ' != ' . Stock::BACKORDERS_NO
            . ' OR '
            . $minQtyExpr . ' <= stock_item.qty'
            . ')'
            . ')';

        $this->getSelect()
            ->joinInner(
                ['stock_item' => $this->getTable('cataloginventory_stock_item')],
                'selection.product_id = stock_item.product_id',
                []
            )->where('(' . $where . ') OR (e.type_id IN ("' . implode('", "', $extendedTypes) . '"))');

        return $this;
    }

    /**
     * @return $this
     */
    public function addFilterByRequiredOptions()
    {
        $this->getSelect()->joinLeft(
            ['cpo' => $this->getTable('catalog_product_option')],
            'e.entity_id = cpo.product_id AND cpo.is_require = 1',
            []
        )->where("cpo.is_require IS NULL");

        return $this;
    }

    /**
     * @param string $tableAlias
     * @return \Zend_Db_Expr
     */
    public function getManageStockExpr($tableAlias = '')
    {
        if ($tableAlias) {
            $tableAlias .= '.';
        }
        $manageStock = $this->getConnection()->getCheckSql(
            $tableAlias . 'use_config_manage_stock = 1',
            $this->getStockConfiguration()->getManageStock(),
            $tableAlias . 'manage_stock'
        );

        return $manageStock;
    }

    /**
     * @param string $tableAlias
     * @return \Zend_Db_Expr
     */
    public function getBackordersExpr($tableAlias = '')
    {
        if ($tableAlias) {
            $tableAlias .= '.';
        }
        $itemBackorders = $this->getConnection()->getCheckSql(
            $tableAlias . 'use_config_backorders = 1',
            $this->getStockConfiguration()->getBackorders(),
            $tableAlias . 'backorders'
        );

        return $itemBackorders;
    }

    /**
     * @param string $tableAlias
     * @return \Zend_Db_Expr
     */
    public function getMinSaleQtyExpr($tableAlias = '')
    {
        if ($tableAlias) {
            $tableAlias .= '.';
        }
        $itemMinSaleQty = $this->getConnection()->getCheckSql(
            $tableAlias . 'use_config_min_sale_qty = 1',
            $this->getStockConfiguration()->getMinSaleQty(),
            $tableAlias . 'min_sale_qty'
        );

        return $itemMinSaleQty;
    }

    /**
     * @return StockConfigurationInterface
     */
    private function getStockConfiguration()
    {
        if (null === $this->stockConfiguration) {
            $this->stockConfiguration = ObjectManager::getInstance()->get(StockConfigurationInterface::class);
        }

        return $this->stockConfiguration;
    }
}
