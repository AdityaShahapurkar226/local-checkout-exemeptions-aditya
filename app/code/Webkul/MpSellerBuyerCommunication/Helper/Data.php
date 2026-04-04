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
namespace Webkul\MpSellerBuyerCommunication\Helper;

use Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication\CollectionFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;

/**
 * MpSellerBuyerCommunication data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Webkul\MpSellerBuyerCommunication\Model\ConversationRepository
     */
    protected $_conversationFactory;

    /**
     * @var \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository
     */
    protected $_sellerCommCollectionFactory;
   /**
    * Constructor
    *
    * @param \Magento\Framework\App\Helper\Context $context
    * @param \Webkul\MpSellerBuyerCommunication\Model\ConversationRepository $conversationFactory
    * @param \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository $sellerCommCollectionFactory
    * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface
    * @param MpHelper $mpHelper
    * @param \Magento\Customer\Model\Customer $customer
    * @param \Magento\Framework\App\Http\Context $httpContext
    */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Webkul\MpSellerBuyerCommunication\Model\ConversationRepository $conversationFactory,
        \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository $sellerCommCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        MpHelper $mpHelper,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Framework\App\Http\Context $httpContext
    ) {
        $this->_conversationFactory = $conversationFactory;
        parent::__construct($context);
        $this->_sellerCommCollectionFactory = $sellerCommCollectionFactory;
        $this->timezoneInterface = $timezoneInterface;
        $this->mpHelper = $mpHelper;
        $this->customerModel = $customer;
        $this->httpContext = $httpContext;
    }

    /**
     * Get admin notification status
     *
     * @return boolean
     */
    public function getAdminNotificationStatus()
    {
        return $this->scopeConfig
        ->getValue(
            'mpsellerbuyercommunication/admin_settings/notification',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Format Date
     *
     * @param DateTime $date
     * @return void
     */
    public function formatDate($date)
    {
        return date("m/d/y g:i A", strtotime($date));
    }

    /**
     * Get Customer Data
     *
     * @param int $customerId
     * @return void
     */
    public function getCustomerData($customerId)
    {
        return $this->customerModel->load($customerId);
    }

    /**
     * Get current customer
     *
     * @return string
     */
    public function getCurrentCustomer()
    {
        return $this->mpHelper->getCustomerId();
    }

    /**
     * Get current time zone
     *
     * @return string
     */
    public function getCurrentTimezone()
    {
        return $this->scopeConfig
        ->getValue(
            'general/locale/timezone',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get all Support type
     *
     * @return array
     */
    public function getSupportTypes()
    {
        return [
               '' => __('--Select--'),
               '0' => __('Presale'),
               '1' => __('Support'),
               '2' => __('Technical'),
               '3' => __('Other')
        ];
    }

    /**
     * Get autoapproval status
     *
     * @return boolean
     */
    public function checkQueryAutoApprovalStatus()
    {
        return $this->scopeConfig
            ->getValue(
                'mpsellerbuyercommunication/admin_settings/autoapproval',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * Get specific support type name by key
     *
     * @param  int $key
     * @return string
     */
    public function getSupportTypeName($key)
    {
        $supportType = $this->getSupportTypes();
        return $supportType[$key];
    }

    /**
     * Get specific status name
     *
     * @param  string $key
     * @return string
     */
    public function getStatus($key)
    {
        return [
            '1' =>  __('Approved'),
            '0' => __('Disapprove')
        ];
    }

    /**
     * Get query status name
     *
     * @param  int $key
     * @return string
     */
    public function getQueryStatusname($key)
    {
        $queryStatus = $this->getQueryStatus();
        return $queryStatus[$key];
    }

    /**
     * Get all query status
     *
     * @return array
     */
    public function getQueryStatus()
    {
        return [
            '0' => __('Pending'),
            '1' => __('Resolved'),
            '2' => __('Closed')
        ];
    }

    /**
     * Get seller filter
     *
     * @return array
     */
    public function getSellerFilter()
    {
        return [
            '' => __('--Select--'),
            'email_id' => __('Email Id'),
            'content' => __('Content')
        ];
    }

    /**
     * Get customer filter
     *
     * @return array
     */
    public function getCustomerFilter()
    {
        return [
            '' => __('--Select--'),
            'product_name' => __('Product Name'),
            'content' => __('Content')
        ];
    }

    /**
     * Get response rate of seller
     *
     * @param  int $productId
     * @return string
     */
    public function getResponseRateOfproduct($productId)
    {
        $conversationIds = [];
        $responseRate = 0;
        $conversationIds = $this->getQueryProductIdsByProductId($productId);

        if (!empty($conversationIds)) {
            $conversationCollection = $this->_conversationFactory
                                    ->getCollectionByQueryIds($conversationIds);

            $queryCount = $this->_conversationFactory->getQueryCount($conversationCollection);
            $replyCount = $this->_conversationFactory->getReplyCount($conversationIds);
            $responseRate = ($replyCount / $queryCount) * 100;
            $responseRate = round($responseRate, 2);
        }
        return $responseRate;
    }

    /**
     * Get response rate of seller
     *
     * @param  int $sellerId
     * @return string
     */
    public function getResponseRateOfSeller($sellerId)
    {
        $conversationIds = [];
        $responseRate = 0;
        $conversationIds = $this->getQueryProductIdsBySellerId($sellerId);
        if (!empty($conversationIds)) {
            $conversationCollection = $this->_conversationFactory
                                    ->getCollectionByQueryIds($conversationIds);

            $queryCount = $this->_conversationFactory->getQueryCount($conversationCollection);
            $replyCount = $this->_conversationFactory->getReplyCount($conversationIds);
           
            if ($queryCount!=0) {
                $responseRate = ($replyCount / $queryCount) * 100;
                $responseRate = round($responseRate, 2);
            }
        }
        
        return $responseRate;
    }

    /**
     * Get response rate of seller
     *
     * @param  int $productId
     * @return string
     */
    public function getResponseTimeOfproduct($productId)
    {
        $conversationIds = [];
        $responseRate = 0;
        $totalResponseTime = 0;
        $conversationIds = $this->getQueryProductIdsByProductId($productId);
        if (!empty($conversationIds)) {
            $conversationCollection = $this->_conversationFactory
                                    ->getResponseCollectionByQueryIds($conversationIds);
            if ($conversationCollection->getSize()) {
                $count = $conversationCollection->getSize();
                foreach ($conversationCollection as $reply) {
                    $inMinutes = $this->getInMinutes($reply->getResponseTime());
                    $totalResponseTime = $totalResponseTime + $inMinutes;
                }
                $responseRate = $totalResponseTime/ $count;
                $rounded = round($responseRate, 2);
            }
        }
        return $responseRate;
    }

    /**
     * Convert hours to minutes
     *
     * @param  string $responseTime
     * @return int
     */
    private function getInMinutes($responseTime)
    {
        $inMinutes = 0;
        $totalMinutes = 0;
        $timeArray = explode(':', $responseTime);

        if ($timeArray[0]) {
            $inMinutes = $timeArray[0]*60;
        }
        if (isset($timeArray[1])) {
            $totalMinutes = $inMinutes + $timeArray[1];
        }
        return $totalMinutes;
    }

    /**
     * Get response rate of seller
     *
     * @param  int $sellerId
     * @return string
     */
    public function getResponseTimeOfSeller($sellerId)
    {
        $conversationIds = [];
        $responseRate = 0;
        $totalResponseTime = 0;
        $rounded = 0;
        $conversationIds = $this->getQueryProductIdsBySellerId($sellerId);
        if (!empty($conversationIds)) {
            $conversationCollection = $this->_conversationFactory
                                    ->getResponseCollectionByQueryIds($conversationIds);
            if ($conversationCollection->getSize()) {
                $count = $conversationCollection->getSize();
                foreach ($conversationCollection as $reply) {
                    $inMinutes = $this->getInMinutes($reply->getResponseTime());
                    $totalResponseTime = $totalResponseTime + $inMinutes;
                }
                $responseRate = $totalResponseTime/ $count;
                $rounded = round($responseRate, 2);
            }
        }
        return $rounded;
    }

    /**
     * Get query list by product id
     *
     * @param int $productId
     * @return array
     */
    public function getQueryProductIdsByProductId($productId)
    {
        $conversationIds = [];
        $productQueryList = $this->_sellerCommCollectionFactory->getAllCollectionByProductId($productId);
        if ($productQueryList->getSize()) {
            foreach ($productQueryList as $conversation) {
                $conversationIds[] = $conversation->getEntityId();
            }
        }
        return $conversationIds;
    }

    /**
     * Get query list by seller id
     *
     * @param int $sellerId
     * @return array
     */
    public function getQueryProductIdsBySellerId($sellerId)
    {
        $conversationIds = [];
        $productQueryList = $this->_sellerCommCollectionFactory->getAllCollectionBySeller($sellerId);
        if ($productQueryList->getSize()) {
            foreach ($productQueryList as $conversation) {
                $conversationIds[] = $conversation->getEntityId();
            }
        }
        return $conversationIds;
    }

    /**
     * Check customer logged in or not
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        return  $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }

    /**
     * Remove scripting from data
     *
     * @param string $msg
     * @return string
     */
    public function removeScriptFromData($msg)
    {
        return preg_replace('#<script(.*?)>(.*?)</script>#is', '', $msg);
    }

    /**
     * Function to get customer id from context
     *
     * @return int customerId
     */
    public function getCustomerId()
    {
        return $this->httpContext->getValue('customer_id');
    }
    /**
     * Get selected order status from admin
     *
     * @return void
     */
    public function getAdminOrderStatus()
    {
        return $this->scopeConfig
        ->getValue(
            'mpsellerbuyercommunication/admin_settings/order_status',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Gest Query Approval
     *
     * @return void
     */
    public function getGuestQueryApproval()
    {
        return $this->scopeConfig
        ->getValue(
            'mpsellerbuyercommunication/admin_settings/guestapproval',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Send Mail From Seller Id
     *
     * @return void
     */
    public function getSendMailFromSellerId()
    {
        return $this->scopeConfig
        ->getValue(
            'mpsellerbuyercommunication/admin_settings/selleremailapproval',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
