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
namespace Webkul\MpAssignProduct\Block\Adminhtml\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Webkul\MpAssignProduct\Model\ResourceModel\Items\CollectionFactory as AssignProductCollection;

class Add extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $mpAssignHelper;

    /**
     * Initialization
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Webkul\MpAssignProduct\Helper\Data $mpAssignHelper
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param AssignProductCollection $assignProductCollection
     * @param ProductCollection $productCollectionFactory
     * @param \Magento\Framework\Pricing\Helper\Data $pricingHelper
     * @param \Magento\Catalog\Helper\Image $catalogImage
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\MpAssignProduct\Model\Config\Source\Sellerlist $sellerlist
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Webkul\MpAssignProduct\Helper\Data $mpAssignHelper,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        AssignProductCollection $assignProductCollection,
        ProductCollection $productCollectionFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Image $catalogImage,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\MpAssignProduct\Model\Config\Source\Sellerlist $sellerlist,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_mpAssignHelper = $mpAssignHelper;
        $this->_productStatus = $productStatus;
        $this->_productVisibility = $productVisibility;
        $this->_assignProductCollection = $assignProductCollection;
        $this->_productCollection = $productCollectionFactory;
        $this->pricingHelper = $pricingHelper;
        $this->catalogImage = $catalogImage;
        $this->_productRepository = $productRepository;
        $this->mpHelper = $mpHelper;
        $this->sellerlist = $sellerlist;
        parent::__construct($context, $data);
    }
    /**
     * Get Searched Query String
     *
     * @return string
     */
    public function getQueryString()
    {
        $queryString = $this->getRequest()->getParam('query');
        if ($queryString) {
            $queryString = strip_tags(trim($queryString));
            $queryString = str_replace('%', '', $queryString);
        }
        
        return $queryString;
    }
    /**
     * Get All Products
     *
     * @return bool|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getAllProducts()
    {
        
        $queryString = $this->getQueryString();
        $page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        $pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 1;

        if ($queryString != '') {
            
            $assignProductIds = $this->_assignProductCollection
                                ->create()->getAllIds();
            // print_r($assignProductIds);
            $allowedTypes = $this->_mpAssignHelper->getAllowedProductTypes();
            $collection = $this->_productCollection
                                ->create()
                                ->addFieldToSelect('*')
                                ->addFieldToFilter('name', ['like' => '%'.$queryString.'%']);
            $collection->addFieldToFilter('type_id', ['in' => $allowedTypes]);
            if (count($assignProductIds) > 0) {
                $collection->addFieldToFilter('entity_id', ['nin' => $assignProductIds]);
            }
           
            $collection->addAttributeToFilter('status', ['in' => $this->_productStatus->getVisibleStatusIds()]);
            $collection->setVisibility($this->_productVisibility->getVisibleInSiteIds());
            $collection->setOrder('created_at', 'desc');
            $collection->setFlag('has_stock_status_filter', true);
        } else {
            $collection = $this->_productCollection
                                ->create()
                                ->addFieldToSelect('*')
                                ->addFieldToFilter('entity_id', 0);
        }
        
        return $collection;
    }
    /**
     * Set Layout for product
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllProducts()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'assign.product.list.pager'
            )
            ->setShowPerPage(true)
            ->setCollection(
                $this->getAllProducts()
            );
            $this->setChild('pager', $pager);
            
        }

        return $this;
    }

    /**
     * Get pager
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get Assign Helper
     *
     * @return void
     */
    public function getAssignHelper()
    {
        return $this->_mpAssignHelper;
    }
    /**
     * GetProductImage function (used to get the product image)
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageType
     * @return string
     */
    public function getProductImage($product, $imageType = "product_thumbnail_image")
    {
        return $this->catalogImage->init($product, $imageType)
                    ->constrainOnly(false)
                    ->keepAspectRatio(true)
                    ->keepFrame(false)
                    ->resize(75)
                    ->getUrl();
    }
    /**
     * GetFormatedPrice function (used to format the price)
     *
     * @param float $price
     * @return string
     */
    public function getFormatedPrice($price = 0)
    {
        return $this->pricingHelper->currency($price, true, false);
    }
    /**
     * GetAddProductPageUrl function (used to get the add assign product page URL)
     *
     * @param integer $productId
     * @return string
     */
    public function getAddProductPageUrl($productId = 0)
    {
        return $this->getUrl('mpassignproduct/product/add', ['id' => $productId]);
    }
    /**
     * Get Product id
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->getRequest()->getParam('id');
    }
    /**
     * Get product by id
     *
     * @param integer $id
     * @return object
     */
    public function getProduct($id = 0)
    {
        $id = $this->getProductId();
        return $this->_productRepository->getById($id);
    }
    /**
     * Get Mp Helper
     *
     * @return object
     */
    public function getMpHelper()
    {
        return $this->mpHelper;
    }

    /**
     * Get Seller List
     *
     * @return array
     */
    public function getSellerList()
    {
        return $this->sellerlist->toOptionArray();
    }
}
