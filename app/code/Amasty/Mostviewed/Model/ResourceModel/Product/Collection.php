<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Product;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * @return $this
     */
    public function updateTotalRecords()
    {
        $this->_totalRecords = count($this->getItems());

        return $this;
    }

    /**
     * @param array $items
     *
     * @return $this
     */
    public function setItems($items)
    {
        $this->_items = $items;

        return $this;
    }

    /**
     * @param array $bundleIds
     *
     * @return $this
     */
    public function applyBundleFilter($bundleIds)
    {
        $this->getSelect()->join(
            ['pack' => $this->getTable(\Amasty\Mostviewed\Model\ResourceModel\Pack::PACK_PRODUCT_TABLE)],
            'e.entity_id = pack.product_id',
            ['bundle_pack_id' => 'pack.pack_id']
        )
            ->where('pack.pack_id IN(?)', $bundleIds);

        return $this;
    }

    /**
     * Get SQL for get record count without left JOINs
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $select = parent::getSelectCountSql();
        $select->reset(\Magento\Framework\DB\Select::GROUP);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);

        return $select;
    }
}
