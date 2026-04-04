<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Model;

use Magento\Framework\App\ResourceConnection;

class SellerIdDataMapper
{

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * Initialization
     *
     * @param ResourceConnection $resource
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $mpProductCollectionFactory
     */
    public function __construct(
        ResourceConnection $resource,
        \Webkul\Marketplace\Helper\Data $helper,
        \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $mpProductCollectionFactory
    ) {
        $this->resource = $resource;
        $this->helper = $helper;
        $this->mpProductCollectionFactory = $mpProductCollectionFactory;
    }

    /**
     * Populate elastic index with seller id for products.
     *
     * @param array                                         $documents
     * @param \Magento\Framework\Search\Request\Dimension[] $dimensions
     * @param string                                        $indexIdentifier
     *
     * @return array
     */
    public function map(array $documents, $dimensions, $indexIdentifier)
    {
      
        if (!$this->helper->allowSellerFilter()) {
            return $documents;
        }
        $scope = $dimensions;
        $sellerProductCollection = $this->mpProductCollectionFactory->create();
       
        $select = $sellerProductCollection->getSelect();
        $select->where('main_table.mageproduct_id IN(?)', array_keys($documents));
        
        $rows = [];
        foreach ($this->resource->getConnection()->fetchAll($select) as $product) {
            
            $rows[$product['mageproduct_id']] = $product['seller_id'];
            
        }
      
        foreach ($documents as $id => $doc) {
            $doc['seller_id']          = isset($rows[$id]) ? (int)$rows[$id] : 0;
            $doc['seller_id' . '_raw'] = isset($rows[$id]) ? (int)$rows[$id] : 0;

            $documents[$id] = $doc;
        
        }

        return $documents;
    }
}
