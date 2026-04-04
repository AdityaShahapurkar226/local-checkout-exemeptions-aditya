<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class ProductDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        $addFieldStrategies = [],
        $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );
        $this->collection->addAttributeToSelect(['status', 'thumbnail', 'name', 'price'], 'left');

        /* don't work with grouped and gift card as a children */
        if ($name == 'amasty_mostviewed_child_product_listing_data_source') {
            $this->collection->addFieldToFilter('type_id', ['nin' => [Grouped::TYPE_CODE, 'giftcard']]);
        }
    }
}
