<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Plugin\Helper;

use Webkul\Marketplace\Model\Product as SellerProduct;

class Data
{
    /**
     * @var \Webkul\MpAssignProduct\Model\ItemsFactory
     */
    protected $assignedItem;

    /** @var \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory */
    protected $_mpProductCollectionFactory;

    /** @var Collection */
    protected $_productCollectionFactory;

    /**
     *
     * @param \Webkul\MpAssignProduct\Model\ItemsFactory $assignedItem
     * @param \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $_mpProductCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collection
     */
    public function __construct(
        \Webkul\MpAssignProduct\Model\ItemsFactory $assignedItem,
        \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $_mpProductCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collection
    ) {
        $this->assignedItem = $assignedItem;
        $this->_mpProductCollectionFactory = $_mpProductCollectionFactory;
        $this->_productCollectionFactory = $collection;
    }

    /**
     * Plugin for getSellerProductDataByProductId
     *
     * @param \Webkul\Marketplace\Helper\Data $subject
     * @param \Closure $proceed
     * @param int $productId
     * @return $result
     */
    public function aroundGetSellerProductDataByProductId(
        \Webkul\Marketplace\Helper\Data $subject,
        \Closure $proceed,
        $productId
    ) {
        $collecton = $proceed($productId);
        if ($collecton->getSize()) {
            return $collecton;
        }
        $assignItem = $this->assignedItem->create()->getCollection();
        $assignItem->addFieldToFilter('assign_product_id', $productId);
        return $assignItem;
    }

    /**
     * Plugin for getSellerIdByProductId
     *
     * @param \Webkul\Marketplace\Helper\Data $subject
     * @param \Closure $proceed
     * @param int $productId
     * @return $result
     */
    public function aroundGetSellerIdByProductId(
        \Webkul\Marketplace\Helper\Data $subject,
        \Closure $proceed,
        $productId
    ) {
        $sellerId = $proceed($productId);
        if ($sellerId) {
            return $sellerId;
        }
        $assignItem = $this->assignedItem->create()->getCollection();
        $assignItem->addFieldToFilter('assign_product_id', $productId);
        return $assignItem->getFirstItem()->getSellerId();
    }

    /**
     * Plugin for getSellerProCount
     *
     * @param \Webkul\Marketplace\Helper\Data $subject
     * @param \Closure $proceed
     * @param int $sellerId
     * @return $result
     */
    public function aroundGetSellerProCount(
        \Webkul\Marketplace\Helper\Data $subject,
        \Closure $proceed,
        $sellerId
    ) {
        
        $assignItem = $this->assignedItem->create()->getCollection();
        $assignItem->addFieldToFilter('seller_id', $sellerId);
        if ($assignItem->getSize() > 0) {
            $querydata = $this->_mpProductCollectionFactory->create()
            ->addFieldToFilter('seller_id', $sellerId)
            ->addFieldToFilter('status', ['neq' => SellerProduct::STATUS_DISABLED])
            ->addFieldToSelect('mageproduct_id')
            ->setOrder('mageproduct_id');
            $assignProductsIds = $assignItem->addFieldToSelect('assign_product_id')->getData();
            $assignProductsIds = array_column($assignProductsIds, 'assign_product_id');
            $assignProductsIds = array_merge($assignProductsIds, $querydata->getData());
            $collection = $this->_productCollectionFactory->create();
            $collection->addAttributeToSelect('*');
            $collection->addAttributeToFilter('entity_id', ['in' => $assignProductsIds]);
            $collection->addAttributeToFilter('visibility', ['in' => [2,4]]);
            $collection->addAttributeToFilter('status', ['neq' => SellerProduct::STATUS_DISABLED]);
            $collection->addStoreFilter();
            return $collection->getSize();
        } else {
            return $proceed($sellerId);
        }
    }
}
