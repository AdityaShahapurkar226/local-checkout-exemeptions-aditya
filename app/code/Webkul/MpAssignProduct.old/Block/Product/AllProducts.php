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
namespace Webkul\MpAssignProduct\Block\Product;

use Webkul\MpAssignProduct\Model\ResourceModel\Items\CollectionFactory;

class AllProducts extends \Magento\Framework\View\Element\Template
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
     * @var CollectionFactory
     */
    protected $_itemsCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_productList;

    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $mpAssignHelper;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CollectionFactory $itemsCollectionFactory
     * @param \Webkul\MpAssignProduct\Helper\Data $mpAssignHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CollectionFactory $itemsCollectionFactory,
        array $data = [],
        \Webkul\MpAssignProduct\Helper\Data $mpAssignHelper = null
    ) {
        $this->_storeManager = $context->getStoreManager();
        $this->_customerSession = $customerSession;
        $this->_itemsCollection = $itemsCollectionFactory;
        $this->mpAssignHelper = $mpAssignHelper ?: \Magento\Framework\App\ObjectManager::getInstance()
                                  ->create(\Webkul\MpAssignProduct\Helper\Data::class);
        parent::__construct($context, $data);
    }

    /**
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Assigned Product List'));
    }

    /**
     * @return bool|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getAllProducts()
    {
        if (!$this->_productList) {
            $customerId = $this->_customerSession->getCustomerId();
            $sellercollection = $this->_itemsCollection
                                ->create()
                                ->addFieldToFilter('seller_id', $customerId);
            $sellercollection->setOrder('created_at', 'desc');
            $this->_productList = $sellercollection;
        }
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
                'assignproduct.all.products.pager'
            )->setCollection(
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
     * productViewUrl function
     *
     * @return string
     */
    public function productViewUrl()
    {
        return $this->getUrl(
            'mpassignproduct/product/view',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
    /**
     * getProductUrl function (used to get the product url)
     *
     * @param integer $productId
     * @return string
     */
    public function getProductUrl($productId = 0)
    {
        return $this->mpAssignHelper->getProduct($productId)->getProductUrl();
    }

    /**
     * getProduct function (used to get the product)
     *
     * @param integer $productId
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct($productId = 0)
    {
        return $this->mpAssignHelper->getProduct($productId);
    }
}
