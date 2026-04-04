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
namespace Webkul\MpSellerBuyerCommunication\Controller\Adminhtml\Query;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication\CollectionFactory;
use Webkul\MpSellerBuyerCommunication\Model\ResourceModel\Conversation\CollectionFactory
as ConversationCollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Customer;
use Magento\Catalog\Model\Product;
use Webkul\Marketplace\Helper\Email;
use Webkul\MpSellerBuyerCommunication\Model\Conversation;

/**
 * Class Query MassDelete
 */
class MassApprove extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Webkul\MpSellerBuyerCommunication\Model\ConversationRepository
     */
    protected $_conversationFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     *
     * @var Product object
     */
    protected $_product;

    /**
     * @var Webkul\Marketplace\Helper\Email
     */
    protected $email;

    /**
     * @var \Webkul\MpSellerBuyerCommunication\Helper\Email
     */
    protected $emailHelper;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var Magento\Framework\App\ResourceConnection
     */
    protected $resource;
    
    /**
     * Constructor
     *
     * @param Customer $customer
     * @param Product $product
     * @param Context $context
     * @param Filter $filter
     * @param Email $email
     * @param CollectionFactory $collectionFactory
     * @param \Webkul\MpSellerBuyerCommunication\Helper\Email $emailHelper
     * @param \Webkul\MpSellerBuyerCommunication\Model\ConversationRepository $conversationFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        Customer $customer,
        Product $product,
        Context $context,
        Filter $filter,
        Email $email,
        CollectionFactory $collectionFactory,
        \Webkul\MpSellerBuyerCommunication\Helper\Email $emailHelper,
        \Webkul\MpSellerBuyerCommunication\Model\ConversationRepository $conversationFactory,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->_customer = $customer;
        $this->_product = $product;
        $this->filter = $filter;
        $this->email = $email;
        $this->emailHelper = $emailHelper;
        $this->_collectionFactory = $collectionFactory;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        $this->_conversationFactory = $conversationFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->_collectionFactory->create());
        $ids = [];
        foreach ($collection as $value) {
            $ids[] = $value->getId();
             /*send mail to seller*/
            $emailTemplateVariables = [];
            $senderInfo = [];
            $receiverInfo = [];
            $buyerEmail = $value->getEmailId();

            $collection = $this->_conversationFactory
                        ->getCollectionByQueryId($value->getEntityId())
                        ->getFirstItem();

            $queryContent = strip_tags($collection->getMessage());

            $buyerData = $this->_customer
                    ->getCollection()->addFieldToFilter('email', ['eq'=> $buyerEmail]);
            if ($buyerData->getSize()) {
                foreach ($buyerData as $buyer) {
                    $buyerId = $buyer->getId();
                    $buyerName = $this->loadCustomerName($buyerId);
                }
            } else {
                $buyerName = 'Guest';
            }
            $seller = $this->loadSellerData($value);
            $emailTemplateVariables['myvar1'] =$seller->getName();
            $sellerEmail = $seller->getEmail();

            $emailTemplateVariables['myvar3'] =$this->getProductName($value);

            $data = [
                'product-id' => $value->getProductId()
            ];
            $emailTemplateVariables['myvar4'] = $queryContent;
            $emailTemplateVariables['myvar5'] = $value->getEmailId();

            $subject = (
              strlen($value->getSubject()) > 50
              ) ? substr($value->getSubject(), 0, 50).' ..' : $value->getSubject();
            $emailTemplateVariables['myvar6'] = $subject;
            $senderInfo = [
                'name' => $buyerName,
                'email' => $buyerEmail,
            ];
            $receiverInfo = [
                'name' => $seller->getName(),
                'email' => $sellerEmail,
            ];
            $this->email->sendQuerypartnerEmail(
                $data,
                $emailTemplateVariables,
                $senderInfo,
                $receiverInfo
            );

            /*send notification mail to customer*/
            $senderInfo = [];
            $receiverInfo = [];
            $emailTemplateVariables['myvar1'] =$buyerName;
            $senderInfo = [
                'name' => $seller->getName(),
                'email' => $sellerEmail,
            ];
            $receiverInfo = [
                'name' => $buyerName,
                'email' => $buyerEmail,
            ];
            $this->emailHelper->sendQuerypartnerEmailToCustomer(
                $data,
                $emailTemplateVariables,
                $senderInfo,
                $receiverInfo
            );
        }
        $update = ['status' => Conversation::STATUS_APPROVE];
        $where = ['entity_id IN (?)' => $ids];

        try {
            $this->connection->beginTransaction();
            $this->connection->update($this->resource->getTableName(Conversation::TABLE_NAME), $update, $where);
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been approved.', count($ids)));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('mpsellerbuyercommunication/query/index/');
    }

    /**
     * Get Product Name
     *
     * @param object $value
     * @return void
     */
    public function getProductName($value)
    {
        $this->_product->load($value->getProductId())->getName();
    }

   /**
    * Load Seller Info
    *
    * @param object $value
    * @return void
    */
    public function loadSellerData($value)
    {
        return $this->_customer->load($value->getSellerId());
    }

    /**
     * Get Customer Name
     *
     * @param int $buyerId
     * @return void
     */
    public function loadCustomerName($buyerId)
    {
        return $this->_customer
          ->load($buyerId)->getName();
    }

    /**
     * Save Status
     *
     * @param object $value
     * @param string $val
     * @return void
     */
    public function saveStatus($value, $val)
    {
        $value->setStatus($val)->save();
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpSellerBuyerCommunication::query_view');
    }
}
