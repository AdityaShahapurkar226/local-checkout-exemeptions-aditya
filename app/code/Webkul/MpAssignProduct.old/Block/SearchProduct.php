<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Block;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use Webkul\MpAssignProduct\Model\ResourceModel\Items\CollectionFactory as AssignProductCollection;
use Webkul\Marketplace\Helper\Data as marketplaceHelper;

class SearchProduct extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $_assignHelper;

    /**
     * @var ProductCollection
     */
    protected $_productCollection;

    /**
     * @var AssignProductCollection
     */
    protected $_assignProductCollection;

    /**
     * @var CollectionFactory
     */
    protected $_mpProductCollection;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_productStatus;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_productVisibility;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_productList;

    /**
     * @var \marketplaceHelper
     */
    protected $marketplaceHelper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $catalogImage;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceInterface;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param ProductCollection $productCollectionFactory
     * @param CollectionFactory $mpProductCollectionFactory
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param AssignProductCollection $assignProductCollection
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param marketplaceHelper $marketplaceHelper
     * @param \Magento\Catalog\Helper\Image $catalogImage
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceInterface
     * @param \Magento\Checkout\Helper\Cart $checkoutHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        ProductCollection $productCollectionFactory,
        CollectionFactory $mpProductCollectionFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        AssignProductCollection $assignProductCollection,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        marketplaceHelper $marketplaceHelper,
        \Magento\Catalog\Helper\Image $catalogImage,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceInterface,
        \Magento\Checkout\Helper\Cart $checkoutHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\ProductRepository $product,
        array $data = []
    ) {
        $this->_storeManager = $context->getStoreManager();
        $this->_customerSession = $customerSession;
        $this->_assignHelper = $helper;
        $this->_productCollection = $productCollectionFactory;
        $this->_mpProductCollection = $mpProductCollectionFactory;
        $this->_productStatus = $productStatus;
        $this->_productVisibility = $productVisibility;
        $this->_assignProductCollection = $assignProductCollection;
        $this->pricingHelper = $pricingHelper;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->catalogImage = $catalogImage;
        $this->priceInterface = $priceInterface;
        $this->checkoutHelper = $checkoutHelper;
        $this->jsonHelper = $jsonHelper;
        $this->product = $product;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Product List'));
    }

    /**
     * @return bool|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getAllProducts()
    {
        if (!$this->_productList) {
            $queryString = $this->getRequest()->getParam('search-product');
            
            $page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
            $pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 1;
            
            if ($queryString != '') {
                $customerId = $this->_customerSession->getCustomerId();
                
                $sellercollection = $this->_mpProductCollection
                                        ->create()
                                        ->addFieldToFilter('seller_id', ['eq' => $customerId])
                                        ->addFieldToSelect('mageproduct_id');
                $products = [];
                foreach ($sellercollection as $data) {
                    array_push($products, $data->getMageproductId());
                }
                $assignProductIds = $this->_assignProductCollection
                                    ->create()->getAllIds();
                $products = array_merge($products, $assignProductIds);
                $sellerAssigncollection = $this->_assignProductCollection
                                ->create()
                                ->addFieldToFilter('seller_id', $customerId)
                                ->addFieldToSelect('product_id');
                foreach ($sellerAssigncollection as $data) {
                    array_push($products, $data->getProductId());
                }

                $allowedTypes = $this->_assignHelper->getAllowedProductTypes();
                $collection = $this->_productCollection
                                    ->create()
                                    ->addFieldToSelect('*')
                                    ->addFieldToFilter('name', ['like' => '%'.$queryString.'%']);
                $collection->addFieldToFilter('type_id', ['in' => $allowedTypes]);
                if (count($products) > 0) {
                    $collection->addFieldToFilter('entity_id', ['nin' => $products]);
                }
                $collection->addAttributeToFilter('status', ['in' => $this->_productStatus->getVisibleStatusIds()]);
                $collection->setVisibility($this->_productVisibility->getVisibleInSiteIds());
                $collection->setOrder('created_at', 'desc');
            } else {
                $collection = $this->_productCollection
                                    ->create()
                                    ->addFieldToSelect('*')
                                    ->addFieldToFilter('entity_id', 0);
            }
            $collection->setPageSize($pageSize);
            $collection->setCurPage($page);
            $this->_productList = $collection;
        }
        // print_r($this->_productList->getSize());exit;
        return $this->_productList;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllProducts()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'mpassignproduct.product.search.pager'
            )
            ->setShowPerPage(true)
            ->setCollection(
                $this->getAllProducts()
            );
            $this->setChild('pager', $pager);
            $this->getAllProducts()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get Current Currency Symbol
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        $symbol = $this->_storeManager->getStore()->getBaseCurrencyCode();
        return $symbol;
    }

    /**
     * Get Helper Object
     *
     * @return object
     */
    public function getHelperObject($helper = '')
    {
        if (empty($helper)) {
            return $this->_assignHelper;
        } else {
            return $this->$helper;
        }
    }
    /**
     *  Catalog Image Helper Object
     */
    public function imageHelperObj()
    {
        return $this->catalogImage;
    }
    /**
     * getAddProductPageUrl function (used to get the add assign product page URL)
     *
     * @param integer $productId
     * @return string
     */
    public function getAddProductPageUrl($productId = 0)
    {
        return $this->getUrl('mpassignproduct/product/add', ['id' => $productId]);
    }
    /**
     * getProductImage function (used to get the product image)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageType
     * @return string
     */
    public function getProductImage($product, $imageType = "category_page_list")
    {
        return $this->catalogImage->init($product, $imageType)
                    ->constrainOnly(false)
                    ->keepAspectRatio(true)
                    ->keepFrame(false)
                    ->resize(75)
                    ->getUrl();
    }
    /**
     * getFormatedPrice function (used to format the price)
     *
     * @param float $price
     * @return string
     */
    public function getFormatedPrice($price = 0)
    {
        return $this->pricingHelper->currency($price, true, false);
    }

    public function getAssociatedProduct($productId)
    {
        $configurable = $this->product->getById($productId);
        if ($configurable->getTypeId() == 'configurable') {
            $children = $configurable
            ->getTypeInstance()
            ->getUsedProducts($configurable);
            return $children;
        }
    }
}
