<?php

namespace Ewave\ExtendedBundleProduct\Model\ResourceModel\Product;

/**
 * Class Collection
 *
 * @package Ewave\ExtendedBundleProduct\Model\ResourceModel\Product
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
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
}
