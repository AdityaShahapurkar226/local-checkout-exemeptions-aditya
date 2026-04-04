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
namespace Webkul\Marketplace\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session as CustomerSession;
use Webkul\Marketplace\Model\ResourceModel\Feedback\CollectionFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Webkul\Marketplace\Model\FeedbackcountFactory;
use Webkul\Marketplace\Model\FeedbackFactory;

/*
 * Webkul Marketplace Seller Feedbackcollection Block
 */
class Feedbackcollection extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customerSession;

    /**
     * @var CollectionFactory
     */
    protected $_feedbackCollectionFactory;

    /** @var \Webkul\Marketplace\Model\Feedback */
    protected $_feedbackList;

    /** @var FeedbackcountFactory */
    protected $feedbackcount;

    /** @var FeedbackFactory */
    protected $feedback;

    /**
     * Construct
     *
     * @param Context $context
     * @param Customer $customer
     * @param CustomerSession $customerSession
     * @param CollectionFactory $feedbackCollectionFactory
     * @param MpHelper $helper
     * @param FeedbackcountFactory $feedbackcount
     * @param FeedbackFactory $feedback
     * @param array $data
     */
    public function __construct(
        Context $context,
        Customer $customer,
        CustomerSession $customerSession,
        CollectionFactory $feedbackCollectionFactory,
        MpHelper $helper,
        FeedbackcountFactory $feedbackcount,
        FeedbackFactory $feedback,
        array $data = []
    ) {
        $this->_feedbackCollectionFactory = $feedbackCollectionFactory;
        $this->_customer = $customer;
        $this->_customerSession = $customerSession;
        $this->helper = $helper;
        $this->feedbackcount = $feedbackcount;
        $this->feedback = $feedback;
        parent::__construct($context, $data);
    }

    /**
     * Get is customer logged in
     *
     * @return bool
     */
    public function getCustomerIsLogin()
    {
        return $this->_customerSession->isLoggedIn();
    }

    /**
     * Get customer name
     *
     * @return string
     */
    public function getCustomerSessionName()
    {
        return $this->_customerSession->getCustomer()->getName();
    }

    /**
     * Set Auth url
     *
     * @return $this
     */
    public function setCustomerSessionAfterAuthUrl()
    {
        $this->_customerSession->setAfterAuthUrl($this->getCurrentUrl());
    }

    /**
     * Get customer
     *
     * @return void
     */
    public function getCustomer()
    {
        return $this->_customer;
    }

    /**
     * Get collection
     *
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getCollection()
    {
        if (!$this->_feedbackList) {
            $collection = [];
            $partner = $this->getProfileDetail();
            if ($partner) {
                $collection = $this->_feedbackCollectionFactory->create()
                ->addFieldToFilter(
                    'status',
                    ['neq' => 0]
                )
                ->addFieldToFilter(
                    'seller_id',
                    ['eq' => $partner->getSellerId()]
                )
                ->setOrder('entity_id', 'DESC');
            }
            $this->_feedbackList = $collection;
        }

        return $this->_feedbackList;
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'marketplace.feedback.pager'
            )
            ->setCollection(
                $this->getCollection()
            );
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }

        return $this;
    }

    /**
     * Get Pager
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get Seller Profile Details
     *
     * @return \Webkul\Marketplace\Model\Seller | bool
     */
    public function getProfileDetail()
    {
        return $this->helper->getProfileDetail(MpHelper::URL_TYPE_FEEDBACK);
    }

    /**
     * Get feed totla
     *
     * @return void
     */
    public function getFeed()
    {
        $partner = $this->getProfileDetail();
        if ($partner) {
            return $this->helper->getFeedTotal($partner->getSellerId());
        } else {
            return [];
        }
    }

    /**
     * Get feed count collecton
     *
     * @return collection
     */
    public function getFeedcountCollection()
    {
        $collection = [];
        $partner = $this->getProfileDetail();
        if ($partner) {
            $collection = $this->feedbackcount->create()
                          ->getCollection()
                          ->addFieldToFilter('buyer_id', $this->_customerSession->getCustomerId())
                          ->addFieldToFilter('seller_id', $partner->getSellerId());
        }

        return $collection;
    }

    /**
     * Get feed collecction
     *
     * @return collection
     */
    public function getFeedCollection()
    {
        $collection = [];
        $partner = $this->getProfileDetail();
        if ($partner) {
            $collection = $this->feedback->create()
                          ->getCollection()
                          ->addFieldToFilter('status', ['neq' => 0])
                          ->addFieldToFilter('seller_id', $partner->getSellerId())
                          ->setOrder('entity_id', 'DESC')
                          ->setPageSize(2)
                          ->setCurPage(1);
        }

        return $collection;
    }

    /**
     * Get current url
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }
    
    /**
     * Get helper
     *
     * @return helper
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
