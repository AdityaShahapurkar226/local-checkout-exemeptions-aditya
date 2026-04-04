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
namespace Webkul\MpAssignProduct\Plugin\Marketplace\Block;

use Webkul\Marketplace\Model\Product as SellerProduct;
use Webkul\Marketplace\Helper\Data as MpHelper;

class Profile
{
    /**
     * @var \Webkul\MpAssignProduct\Model\ItemsFactory
     */
    protected $assignedItem;

    /** @var \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory */
    protected $_mpProductCollectionFactory;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory */
    protected $_productCollectionFactory;

    /** @var MpHelper */
    protected $mpHelper;

    /** @var \Webkul\Marketplace\Model\ProductFactory */
    protected $mpProductModel;

   /**
    * Initialization
    *
    * @param \Webkul\MpAssignProduct\Model\ItemsFactory $assignedItem
    * @param \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $_mpProductCollectionFactory
    * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collection
    * @param MpHelper $mpHelper
    * @param \Webkul\Marketplace\Model\ProductFactory $mpProductModel
    */
    public function __construct(
        \Webkul\MpAssignProduct\Model\ItemsFactory $assignedItem,
        \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $_mpProductCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collection,
        MpHelper $mpHelper,
        \Webkul\Marketplace\Model\ProductFactory $mpProductModel
    ) {
        $this->assignedItem = $assignedItem;
        $this->_mpProductCollectionFactory = $_mpProductCollectionFactory;
        $this->_productCollectionFactory = $collection;
        $this->mpHelper = $mpHelper;
        $this->mpProductModel = $mpProductModel;
    }

    /**
     * Plugin for getSellerProCount
     *
     * @param \Webkul\Marketplace\Helper\Data $subject
     * @param \Closure $proceed
     * @return $result
     */
    public function aroundGetBestsellProducts(
        \Webkul\Marketplace\Block\Profile $subject,
        \Closure $proceed
    ) {
        $partner = $this->mpHelper->getProfileDetail(MpHelper::URL_TYPE_COLLECTION);
        $sellerId = $partner->getSellerId();
        $assignItem = $this->assignedItem->create()->getCollection();
        $assignItem->addFieldToFilter('seller_id', $sellerId);
        if ($assignItem->getSize() > 0) {
            if ($partner) {
                $catalogProductWebsite = $this->_mpProductCollectionFactory->create()
                ->getTable('catalog_product_website');
                $helper = $this->mpHelper;
                if (count($helper->getAllWebsites()) == 1) {
                    $websiteId = 0;
                } else {
                    $websiteId = $helper->getWebsiteId();
                }
                $querydata = $this->mpProductModel->create()
                                    ->getCollection()
                                    ->addFieldToFilter(
                                        'seller_id',
                                        ['eq' => $partner->getSellerId()]
                                    )
                                    ->addFieldToFilter(
                                        'status',
                                        ['neq' => 2]
                                    )
                                    ->addFieldToSelect('mageproduct_id')
                                    ->setOrder('mageproduct_id');
                                
                $assignProductsIds = $assignItem->addFieldToSelect('assign_product_id')->getData();
                $assignProductsIds = array_column($assignProductsIds, 'assign_product_id');
                $assignProductsIds = array_merge($assignProductsIds, $querydata->getData());
                $products = $this->_productCollectionFactory->create();
                $products->addAttributeToSelect('*');
                $products->addAttributeToFilter('entity_id', ['in' => $assignProductsIds]);
                $products->addAttributeToFilter('visibility', ['in' => [4]]);
                $products->addAttributeToFilter('status', 1);
                if ($websiteId) {
                    $products->getSelect()
                    ->join(
                        ['cpw' => $catalogProductWebsite],
                        'cpw.product_id = e.entity_id'
                    )->where(
                        'cpw.website_id = '.$websiteId
                    );
                }
                $products->setPageSize(4)->setCurPage(1)->setOrder('entity_id');
            }
    
            return $products;
        } else {
            return $proceed();
        }
    }
}
