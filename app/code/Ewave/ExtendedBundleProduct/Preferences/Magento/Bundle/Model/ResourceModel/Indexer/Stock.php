<?php
namespace Ewave\ExtendedBundleProduct\Preferences\Magento\Bundle\Model\ResourceModel\Indexer;

use Magento\Catalog\Api\Data\ProductInterface;

class Stock extends \Magento\Bundle\Model\ResourceModel\Indexer\Stock
{
    /**
     * @param null $entityIds
     * @param bool $usePrimaryTable
     * @return $this|\Magento\Bundle\Model\ResourceModel\Indexer\Stock
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareBundleOptionStockData($entityIds = null, $usePrimaryTable = false)
    {
        $this->_cleanBundleOptionStockData();
        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $idxTable = $usePrimaryTable ? $this->getMainTable() : $this->getIdxTable();
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            ['product' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        );
        $select->join(
            ['bo' => $this->getTable('catalog_product_bundle_option')],
            "bo.parent_id = product.$linkField",
            []
        );

//        Removed "required_options" check
//        $status = new \Zend_Db_Expr(
//            'MAX(' . $connection->getCheckSql('e.required_options = 0', 'i.stock_status', '0') . ')'
//        );

        $status = new \Zend_Db_Expr('MAX(i.stock_status)');

        $select->join(
            ['cis' => $this->getTable('cataloginventory_stock')],
            '',
            ['website_id', 'stock_id']
        )->joinLeft(
            ['bs' => $this->getTable('catalog_product_bundle_selection')],
            'bs.option_id = bo.option_id',
            []
        )->joinLeft(
            ['i' => $idxTable],
            'i.product_id = bs.product_id AND i.website_id = cis.website_id AND i.stock_id = cis.stock_id',
            []
        )->joinLeft(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.entity_id = bs.product_id',
            []
        )->group(
            ['product.entity_id', 'cis.website_id', 'cis.stock_id', 'bo.option_id']
        )->columns(
            ['option_id' => 'bo.option_id', 'status' => $status]
        );

        if ($entityIds !== null) {
            $select->where('product.entity_id IN(?)', $entityIds);
        }

        // clone select for bundle product without required bundle options
        $selectNonRequired = clone $select;

        $select->where('bo.required = ?', 1);
        $selectNonRequired->where('bo.required = ?', 0)->having($status . ' = 1');
        $query = $select->insertFromSelect($this->_getBundleOptionTable());
        $connection->query($query);

        $query = $selectNonRequired->insertFromSelect($this->_getBundleOptionTable());
        $connection->query($query);

        return $this;
    }
}
