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
/**
 * MpSellerBuyerCommunication Sendmail controller.
 *
 */
namespace Webkul\MpSellerBuyerCommunication\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\Customer;
use Magento\Catalog\Model\Product;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

use Webkul\MpSellerBuyerCommunication\Model\SellerBuyerCommunication;

class SendOrdermail extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customer;

    /**
     *
     * @var Product
     */
    protected $_product;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Webkul\MpSellerBuyerCommunication\Helper\Data
     */
    protected $_helper;

   /**
    * Constructor
    *
    * @param Context $context
    * @param Session $customerSession
    * @param Customer $customer
    * @param Product $product
    * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
    * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
    * @param PageFactory $resultPageFactory
    * @param \Webkul\MpSellerBuyerCommunication\Helper\Data $helper
    * @param TimezoneInterface $localeDate
    * @param SellerBuyerCommunication $sellerBuyerComm
    * @param \Webkul\MpSellerBuyerCommunication\Model\Conversation $conversation
    * @param \Webkul\Marketplace\Helper\Email $email
    * @param \Webkul\MpSellerBuyerCommunication\Helper\Email $emailHelper
    * @param \Magento\Framework\Json\Helper\Data $jsonData
    * @param \Webkul\Marketplace\Helper\Data $data
    * @param \Webkul\Marketplace\Model\SellerFactory $sellerFactory
    * @param \Webkul\Marketplace\Model\OrdersFactory $mpOrderModel
    * @param \Magento\Sales\Api\Data\OrderInterface $order
    * @param \Magento\Framework\Json\Helper\Data $jsonHelper
    * @param \Webkul\MpSellerBuyerCommunication\Controller\Seller\SaveFiles $saveFiles
    */
    public function __construct(
        Context $context,
        Session $customerSession,
        Customer $customer,
        Product $product,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        PageFactory $resultPageFactory,
        \Webkul\MpSellerBuyerCommunication\Helper\Data $helper,
        TimezoneInterface $localeDate,
        SellerBuyerCommunication $sellerBuyerComm,
        \Webkul\MpSellerBuyerCommunication\Model\Conversation $conversation,
        \Webkul\Marketplace\Helper\Email $email,
        \Webkul\MpSellerBuyerCommunication\Helper\Email $emailHelper,
        \Magento\Framework\Json\Helper\Data $jsonData,
        \Webkul\Marketplace\Helper\Data $data,
        \Webkul\Marketplace\Model\SellerFactory $sellerFactory,
        \Webkul\Marketplace\Model\OrdersFactory $mpOrderModel,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MpSellerBuyerCommunication\Controller\Seller\SaveFiles $saveFiles
    ) {
        $this->_customerSession = $customerSession;
        $this->customer = $customer;
        $this->_product = $product;
        $this->resultPageFactory = $resultPageFactory;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_date = $date;
        $this->_helper = $helper;
        $this->localeDate = $localeDate;
        $this->sellerBuyerComm = $sellerBuyerComm;
        $this->conversation = $conversation;
        $this->email = $email;
        $this->emailHelper = $emailHelper;
        $this->jsonData = $jsonData;
        $this->data = $data;
        $this->sellerFactory = $sellerFactory;
        $this->mpOrderModel = $mpOrderModel;
        $this->order = $order;
        $this->jsonHelper = $jsonHelper;
        $this->saveFiles = $saveFiles;
        parent::__construct($context);
    }

    /**
     * Send mail to seller and save message in database
     *
     * @return json string
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $fileData = $this->getRequest()->getFiles();
        $data['fileData'] = $fileData;
        if ($data['seller-id'] == '') {
            $data['seller-id'] = 0;
        }
        
        $currentDate = $this->localeDate->date()->format('Y-m-d H:i:s');
        $validSeller = 1;
        if ($data['seller-id'] > 0) {
            $validSeller = 0;
            $sellerInfo = $this->sellerFactory->create()->getCollection();
            $customerTable = $sellerInfo->getTable('customer_entity');
            $sellerInfo->getSelect()->join(
                [
                    'ce' => $customerTable
                ],
                'ce.entity_id = main_table.seller_id AND ce.is_active = 1 AND main_table.is_seller = 1 
                AND main_table.seller_id = '.$data['seller-id'],
                [
                    'is_active' => 'ce.is_active'
                ]
            );
            if (count($sellerInfo->getData())) {
                $validSeller = 1;
            }
        }
        if ($validSeller) {
            $this->_eventManager->dispatch(
                'mp_send_querymail',
                [$data]
            );
            if (isset($data['product-id'])) {
                $productId = $data['product-id'];
                $productName = $this->_product->load($productId)->getName();
                ;
            } else {
                $productId = '';
                $productName = '';
            }
            if (isset($data['order-id'])) {
                $orderId = $data['order-id'];
                ;
            } else {
                $orderId = '';
            }
            $adminStoreEmail = $this->data->getAdminEmailId();
            $adminEmail = $adminStoreEmail? $adminStoreEmail:$this->data
                ->getDefaultTransEmailId();
            $sellerId = $data['seller-id'];
            $order = $this->getSellerOrderInfo($orderId);
            $ask = $this->_helper->removeScriptFromData($data['ask']);
            $subject = $data['subject'];
            $buyerEmail = $order->getCustomerEmail();
            $buyerName = $order->getCustomerFirstname().' '.$order->getCustomerLastname();
            $supportType = $data['support_type'];
            $buyerId = $order->getCustomerId();
            $queryStatus = 0;
            $seller = $this->customer->load($data['seller-id']);
            if (count($seller->getData()) == 0) {
                $sellerName = 'Administrator';
                $sellerEmail = $adminEmail;
            } else {
                $sellerEmail = $seller->getEmail();
                $sellerName = $seller->getFirstname().' '.$seller->getLastname();
            }

            $status = $this->_helper->checkQueryAutoApprovalStatus();
            if (!$status) {
                $status = 0;
            }
            /*save new message in database*/
            $model = $this->sellerBuyerComm;
            $model->setProductId($productId);
            $model->setProductName($productName);
            $model->setCustomerName($buyerName);
            $model->setSellerId($sellerId);
            $model->setOrderId($orderId);
            $model->setCreatedAt($currentDate);
            $model->setUpdatedAt($currentDate);
            $model->setCustomerId($buyerId);
            $model->setSubject($subject);
            $model->setEmailId($buyerEmail);
            $model->setSupportType($supportType);
            $model->setStatus($status);
            $model->setQueryStatus($queryStatus);
            $saved = $model->save();
            $lastId = $saved->getId();
            $model = $this->conversation;
            $model->setCommId($lastId);
            $model->setMessage($ask);
            $model->setSender($buyerName);
            $model->setSenderType(0);
            $model->setCreatedAt($currentDate);
            $model->setUpdatedAt($currentDate);
            $converId = $model->save()->getId();
            $data['id'] = $converId;
            $this->saveFiles->saveAttachmentFiles($data);
            /*send mail to seller*/

            if ($status) {
                $emailTemplateVariables = [];
                $senderInfo = [];
                $receiverInfo = [];
                $adminUsername = 'Administrator';
                $emailTemplateVariables['myvar1'] = $sellerName;
                
                if (!isset($data['order-id'])) {
                    $emailTemplateVariables['myvar3'] = "";
                } else {
                    $emailTemplateVariables['myvar3'] =$data['order-id'];
                }
                $emailTemplateVariables['myvar4'] = $data['ask'];
                $emailTemplateVariables['myvar5'] =$buyerEmail;
                $sendEmailFromSeller = $this->_helper->getSendMailFromSellerId();
                $subject = (strlen($data['subject']) > 50) ? substr($data['subject'], 0, 50).'..' : $data['subject'];
                $emailTemplateVariables['myvar6'] =$subject;
                if ($sendEmailFromSeller == '1') {
                    $senderName = $this->jsonHelper->jsonDecode($this->jsonHelper->jsonEncode($buyerName));
                    $senderEmail = $buyerEmail;
                } else {
                    $senderName = $this->jsonHelper->jsonDecode($this->jsonHelper->jsonEncode($adminUsername));
                    $senderEmail = $adminEmail;
                }
                $senderInfo = [
                    'name' => $senderName ,
                    'email' => $senderEmail,
                ];
                $receiverInfo = [
                    'name' => $sellerName,
                    'email' => $sellerEmail,
                ];
                $this->emailHelper->sendOrderQuerypartnerEmailToCustomer(
                    $data,
                    $emailTemplateVariables,
                    $senderInfo,
                    $receiverInfo
                );
                
            }

            /*send notification mail to admin if enabled*/
            if ($this->_helper->getAdminNotificationStatus() && $data['seller-id'] > 0) {
                $senderInfo = [];
                $receiverInfo = [];
                $adminStoreEmail = $this->data->getAdminEmailId();
                $adminEmail=$adminStoreEmail? $adminStoreEmail:$this->data
                ->getDefaultTransEmailId();
                $adminUsername = 'Admin';
                $emailTemplateVariables['myvar1'] = $adminUsername;
                if (!isset($data['order-id'])) {
                   
                    $emailTemplateVariables['myvar3'] ='';
                } else {
                    $emailTemplateVariables['myvar3'] = $data['order-id'];
                }
                $emailTemplateVariables['myvar6'] =$data['subject'];
                $emailTemplateVariables['myvar7'] =$buyerName;
                $emailTemplateVariables['myvar8'] =$buyerEmail;
                $senderInfo = [
                    'name' => $buyerName,
                    'email' => $buyerEmail,
                ];
                $receiverInfo = [
                    'name' => $adminUsername,
                    'email' => $adminEmail,
                ];
                $this->emailHelper->sendOrderQuerypartnerEmailToAdminFromCustomer(
                    $data,
                    $emailTemplateVariables,
                    $senderInfo,
                    $receiverInfo
                );
            }
        }
        $this->getResponse()->representJson(
            $this->jsonData->jsonEncode($converId)
        );
    }

    /**
     * Load Customer Data
     *
     * @param int $buyerId
     * @return void
     */
    public function loadCustomerData($buyerId)
    {
        return $this->customer->load($buyerId)->getName();
    }

    /**
     * Get Seller Order Info
     *
     * @param string $orderId
     * @return void
     */
    public function getSellerOrderInfo($orderId = '')
    {
        $collection = $this->order->load($orderId);
        return $collection;
    }
}
