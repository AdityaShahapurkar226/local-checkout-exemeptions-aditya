<?php
/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MpSellerBuyerCommunication
 * @author      Webkul
 * @copyright   Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */

namespace Webkul\MpSellerBuyerCommunication\Block\Customer;

use Magento\Customer\Model\Customer;
use \Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication\CollectionFactory;
use Webkul\MpSellerBuyerCommunication\Model\ResourceModel\Conversation\Collection;
use Webkul\MpSellerBuyerCommunication\Helper\Data;

class History extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var CollectionFactory
     */
    protected $_sellerCommCollectionFactory;

    /** @var \Webkul\MpSellerBuyerCommunication\Model\SellerBuyerCommunication */
    protected $sellerBuyerCommunicationLists;

    /**
     *
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    public $_productRepository;

    /**
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $_productCollection;

    /**
     * @var Collection;
     */
    public $collection;

    /**
     * Constructor
     *
     * @param Customer $customer
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param CollectionFactory $sellerCommCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
     * @param Collection $collection
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Customer $customer,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionFactory $sellerCommCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        Collection $collection,
        Data $helperData,
        array $data = []
    ) {
        $this->_sellerCommCollectionFactory = $sellerCommCollectionFactory;
        $this->customer = $customer;
        $this->_customerSession = $customerSession;
        $this->imageHelper = $context->getImageHelper();
        $this->_productRepository = $productRepository;
        $this->_productCollection = $productCollection;
        $this->helperData = $helperData;
        parent::__construct($context, $data);
        $this->collection = $collection;
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Seller Communication History'));
    }

    /**
     * Get customer id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    /**
     * Get customer name
     *
     * @param  int $customerId
     * @return string
     */
    public function getCustomerNameId($customerId)
    {
        return $this->customer->load($customerId)->getName();
    }

    /**
     * Get product url
     *
     * @param  int $productId
     * @return string
     */
    public function getProductUrlById($productId)
    {
        try {
            $product = $this->_productRepository->getById($productId);
            if ($product->getStatus()==2) {
                return '#';
            }
            return $product->getProductUrl();
        } catch (\Exception $e) {
            return '#';
        }
    }

    /**
     * Get Product name
     *
     * @param  int $productId
     * @return string
     */
    public function getProductNameById($productId)
    {
        try {
            $product = $this->_productRepository->getById($productId);
            return $product->getName();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get All Communication Data
     *
     * @return void
     */
    public function getAllCommunicationData()
    {
        if (!($sellerId = $this->getCustomerId())) {
            return false;
        }
        if (!$this->sellerBuyerCommunicationLists) {
            $collection = $this->_sellerCommCollectionFactory->create()
            ->addFieldToFilter(
                'customer_id',
                $sellerId
            );
            $filterText = '';
            $paramData = $this->getRequest()->getParams();

            if (!empty($paramData['filter_by']) && !empty($paramData['s'])) {
                $filterText = $paramData['s'] != ""?$paramData['s']:"";

                if ($paramData['filter_by'] == 'product_name') {
                    $productIds = $this->getFilterCollectionByProductName($filterText);

                    $collection
                    ->addFieldToFilter(
                        'product_id',
                        ['in'=>$productIds]
                    );
                } else {
                    $collection = $this->getFilterCollectionByContent($filterText, $collection);
                }
            }

            $collection->setOrder(
                'created_at',
                'desc'
            );

            $this->sellerBuyerCommunicationLists = $collection;
        }

        return $this->sellerBuyerCommunicationLists;
    }

    /**
     * Filter by product name
     *
     * @param  string $filterProduct
     * @return object
     */
    public function getFilterCollectionByProductName($filterProduct)
    {
        $collectionfetch = $this->_productCollection->create()
                   ->addAttributeToSelect('*')
                   ->addFieldToFilter('name', ['like'=>"%".$filterProduct."%"]);
        return $collectionfetch->getAllIds();
    }

    /**
     * Filter by content
     *
     * @param  string $filterText
     * @param  object $collection
     * @return object
     */
    public function getFilterCollectionByContent($filterText, $collection)
    {
        $coversationTable = $this->collection->getTable('marketplace_sellerbuyercommunication_conversation');

        $collection->getSelect()->joinLeft(
            $coversationTable.' as cpev',
            'main_table.entity_id = cpev.comm_id',
            ['comm_id','message']
        )->where(
            "cpev.message like '%".$filterText."%' OR
            main_table.subject like '%".$filterText."%'"
        );
        $collection->getSelect()->group('comm_id');

        return $collection;
    }

    /**
     * Prepare Layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllCommunicationData()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'MpSellerBuyerCommunication.seller.pager'
            )
            ->setCollection(
                $this->getAllCommunicationData()
            );
            $this->setChild('pager', $pager);
            $this->getAllCommunicationData()->load();
        }
        return $this;
    }

    /**
     * Get Pager Html
     *
     * @return void
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get current url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl(); // Give the current url of recently viewed page
    }

    /**
     * Get Helper in view
     */
    public function getHelperCustomerView()
    {
        return $this->helperData;
    }

    /**
     * Get Request
     */
    public function requestData()
    {
        return $this->getRequest()->getParams();
    }

    /**
     * Is Secure Data
     */
    public function isSecureData()
    {
        return $this->getRequest()->isSecure();
    }
}
