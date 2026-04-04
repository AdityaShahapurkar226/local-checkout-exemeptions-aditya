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

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * MpSellerBuyerCommunication Email helper
 */
class Email extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const XML_PATH_EMAIL_ASK_PRODUCT_QUERY_TO_ADMIN =
     'mpsellerbuyercommunication/email/askproductquery_admin_template';
    public const XML_PATH_EMAIL_ASK_PRODUCT_QUERY_TO_CUSTOMER   =
    'mpsellerbuyercommunication/email/askproductquery_customer_template';
    public const XML_PATH_EMAIL_CONVERSATION_TO_ADMIN   =
    'mpsellerbuyercommunication/email/conversation_admin_template';
    public const XML_PATH_EMAIL_CONVERSATION   = 'mpsellerbuyercommunication/email/conversation_template';
    public const XML_PATH_EMAIL_PRODUCT_QUERY = 'marketplace/email/askproductquery_seller_template';
    public const XML_PATH_EMAIL_SELLER_QUERY = 'marketplace/email/askquery_seller_template';
    public const XML_PATH_ORDER_EMAIL_NOTIFICATION_TO_CUSTOMER   =
    'mpsellerbuyercommunication/email/askorderquery_fromseller_template';
    public const XML_PATH_ORDER_EMAIL_NOTIFICATION_TO_ADMIN   =
    'mpsellerbuyercommunication/email/askorderquery_fromseller_template_toadmin';
    public const XML_PATH_ORDER_EMAIL_NOTIFICATION_TO_ADMIN_FROM_CUSTOMER   =
    'mpsellerbuyercommunication/email/askorderquery_fromcustomer_template_toadmin';
    public const XML_PATH_ORDER_EMAIL_NOTIFICATION_FROM_ADMIN   =
    'mpsellerbuyercommunication/email/askorderquery_fromadmin_template';
    
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     *
     * @var $tempId
     */
    protected $tempId;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Webkul\MpSellerBuyerCommunication\Logger\Logger $logger
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Framework\Filesystem\Io\File $file
     * @param \Webkul\MpSellerBuyerCommunication\Model\ConversationRepository $conversationFactory
     * @param \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository $communicationRepository
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\MpSellerBuyerCommunication\Logger\Logger $logger,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Filesystem\Io\File  $file,
        \Webkul\MpSellerBuyerCommunication\Model\ConversationRepository $conversationFactory,
        \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository $communicationRepository
    ) {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->logger = $logger;
        $this->fileSystem = $fileSystem;
        $this->file = $file;
        $this->_conversationFactory = $conversationFactory ?: \Magento\Framework\App\ObjectManager::getInstance()
                                ->create(\Webkul\MpSellerBuyerCommunication\Model\ConversationRepository::class);
        $this->_communicationRepository =
        $communicationRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
        ->create(\Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository::class);
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * Return store
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * GenerateTemplate description
     *
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template =  $this->_transportBuilder->setTemplateIdentifier($this->tempId)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $this->_storeManager->getStore()->getId(),
                    ]
                )
                ->setTemplateVars($emailTemplateVariables)
                ->setFrom($senderInfo)
                ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this;
    }

    /**
     * SendQuerypartnerEmail description
     *
     * @param  Mixed $data
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function sendQuerypartnerEmailToCustomer($data, $emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->tempId = $this->getTemplateId(self::XML_PATH_EMAIL_ASK_PRODUCT_QUERY_TO_CUSTOMER);
        $this->inlineTranslation->suspend();

        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }

        $this->inlineTranslation->resume();
    }

    /**
     * SendQuerypartnerEmail description
     *
     * @param  Mixed $data
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function sendQuerypartnerEmailToAdmin($data, $emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->tempId = $this->getTemplateId(self::XML_PATH_EMAIL_ASK_PRODUCT_QUERY_TO_ADMIN);
        $this->inlineTranslation->suspend();
       
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }

        $this->inlineTranslation->resume();
    }

    /**
     * SendQuerypartnerEmail description
     *
     * @param  Mixed $data
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function sendCommunicationEmail($data, $emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->tempId = $this->getTemplateId(self::XML_PATH_EMAIL_CONVERSATION);
        
        $collection = $this->_conversationFactory->getCollectionByEntityId($data['lastConvId']);
        $imageUploadPath = $this->fileSystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath('sellerbuyercommunication/'.$collection->getCommId().'/'.$collection->getEntityId().'/');
         //actual image name
        
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        if (!empty($collection->getAttachments()) || $collection->getAttachments() != '') {
            $attachments = explode(',', $collection->getAttachments());
            $attachmentCount = count($attachments);
            for ($i=0; $i < $attachmentCount; $i++) {
                $mediaPath = $imageUploadPath.$attachments[$i]; //set image path
                $body = $this->file->read($mediaPath);
                $mimetype = mime_content_type($mediaPath);
                $this->_transportBuilder->addAttachment(
                    $body,
                    $attachments[$i],
                    $mimetype
                );
            }
        }
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->inlineTranslation->resume();
    }
    /**
     * Send Communication Email To Seller
     *
     * @param array $data
     * @param array $emailTemplateVariables
     * @param array $senderInfo
     * @param array $receiverInfo
     * @return void
     */
    public function sendCommunicationEmailToSeller($data, $emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->tempId = $this->getTemplateId(self::XML_PATH_EMAIL_CONVERSATION);
        
        $collection = $this->_conversationFactory->getCollectionByEntityId($data['lastConvId']);
        $imageUploadPath = $this->fileSystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath('sellerbuyercommunication/'.$collection->getCommId().'/'.$collection->getEntityId().'/');
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        if (!empty($collection->getAttachments()) || $collection->getAttachments() != '') {
            $attachments = explode(',', $collection->getAttachments());
            $attachmentCount = count($attachments);
            for ($i=0; $i < $attachmentCount; $i++) {
                $mediaPath = $imageUploadPath.$attachments[$i]; //set image path
                $body = $this->file->read($mediaPath);
                $mimetype = mime_content_type($mediaPath);
                $this->_transportBuilder->addAttachment(
                    $body,
                    $attachments[$i],
                    $mimetype
                );
            }
        }
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->inlineTranslation->resume();
    }

    /**
     * SendQuerypartnerEmail description
     *
     * @param  Mixed $data
     * @param  Mixed $emailTemplateVariables
     * @param  Mixed $senderInfo
     * @param  Mixed $receiverInfo
     * @return void
     */
    public function sendCommunicationEmailToAdmin($data, $emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->tempId = $this->getTemplateId(self::XML_PATH_EMAIL_CONVERSATION_TO_ADMIN);
        $this->inlineTranslation->suspend();

        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->inlineTranslation->resume();
    }
    
    /**
     * Send Query Partner Email
     *
     * @param array $data
     * @param array $emailTemplateVariables
     * @param array $senderInfo
     * @param array $receiverInfo
     * @return void
     */
    public function sendQuerypartnerEmail($data, $emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        if (isset($data['product-id']) && $data['product-id']) {
            $this->tempId = $this->getTemplateId(self::XML_PATH_EMAIL_PRODUCT_QUERY);
        } else {
            $this->tempId = $this->getTemplateId(self::XML_PATH_EMAIL_SELLER_QUERY);
        }
        $fileData = $data['fileData']['img_attachment'];
        
        $collection = $this->_conversationFactory->getCollectionByEntityId($data['id']);
        $imageUploadPath = $this->fileSystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath('sellerbuyercommunication/'.$collection->getCommId().'/'.$collection->getEntityId().'/');
        $commConnection  = $this->_communicationRepository
                            ->getCollectionByEntityId($collection->getCommId());
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        if (!empty($collection->getAttachments()) || $collection->getAttachments() != '') {
            $attachments = explode(',', $collection->getAttachments());
            $attachmentCount = count($attachments);
            for ($i=0; $i < $attachmentCount; $i++) {
                $mediaPath = $imageUploadPath.$attachments[$i]; //set image path
                $body = $this->file->read($mediaPath);
                $mimetype = mime_content_type($mediaPath);
                $this->_transportBuilder->addAttachment(
                    $body,
                    $attachments[$i],
                    $mimetype
                );
            }
        }
    
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }

        $this->inlineTranslation->resume();
    }

    /**
     * Send Orer Query Partner Email To Cutomer
     *
     * @param array $data
     * @param array $emailTemplateVariables
     * @param array $senderInfo
     * @param array $receiverInfo
     * @return void
     */
    public function sendOrderQuerypartnerEmailToCustomer($data, $emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->tempId = $this->getTemplateId(self::XML_PATH_ORDER_EMAIL_NOTIFICATION_TO_CUSTOMER);
        $collection = $this->_conversationFactory->getCollectionByEntityId($data['id']);
        $imageUploadPath = $this->fileSystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath('sellerbuyercommunication/'.$collection->getCommId().'/'.$collection->getEntityId().'/');
        $commConnection  = $this->_communicationRepository
                            ->getCollectionByEntityId($collection->getCommId());
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        if (!empty($collection->getAttachments()) || $collection->getAttachments() != '') {
            $attachments = explode(',', $collection->getAttachments());
            $attachmentCount = count($attachments);
            for ($i=0; $i < $attachmentCount; $i++) {
                
                $mediaPath = $imageUploadPath.$attachments[$i]; //set image path
                $body = $this->file->read($mediaPath);
                $mimetype = mime_content_type($mediaPath);
                $this->_transportBuilder->addAttachment(
                    $body,
                    $attachments[$i],
                    $mimetype
                );
            }
        }
        
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->inlineTranslation->resume();
    }
    /**
     * Send Order Query Partner Email To Admin
     *
     * @param array $data
     * @param array $emailTemplateVariables
     * @param array $senderInfo
     * @param array $receiverInfo
     * @return void
     */
    public function sendOrderQuerypartnerEmailToAdmin($data, $emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->tempId = $this->getTemplateId(self::XML_PATH_ORDER_EMAIL_NOTIFICATION_TO_ADMIN);
        $this->inlineTranslation->suspend();

        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->inlineTranslation->resume();
    }
    /**
     * Send Order Query Partner Email To Admin From Customer
     *
     * @param array $data
     * @param array $emailTemplateVariables
     * @param array $senderInfo
     * @param array $receiverInfo
     * @return void
     */
    public function sendOrderQuerypartnerEmailToAdminFromCustomer(
        $data,
        $emailTemplateVariables,
        $senderInfo,
        $receiverInfo
    ) {
        $this->tempId = $this->getTemplateId(self::XML_PATH_ORDER_EMAIL_NOTIFICATION_TO_ADMIN_FROM_CUSTOMER);
        $this->inlineTranslation->suspend();

        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->inlineTranslation->resume();
    }
    /**
     * Send Order Query Partner To Customer From Admin
     *
     * @param array $data
     * @param array $emailTemplateVariables
     * @param array $senderInfo
     * @param array $receiverInfo
     * @return void
     */
    public function sendOrderQueryEmailToCustomerFromAdmin($data, $emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $this->tempId = $this->getTemplateId(self::XML_PATH_ORDER_EMAIL_NOTIFICATION_FROM_ADMIN);
        $collection = $this->_conversationFactory->getCollectionByEntityId($data['id']);
        $imageUploadPath = $this->fileSystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath('sellerbuyercommunication/'.$collection->getCommId().'/'.$collection->getEntityId().'/');
        $commConnection  = $this->_communicationRepository
                            ->getCollectionByEntityId($collection->getCommId());
        
         //actual image name
        
        $this->inlineTranslation->suspend();
        $this->generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo);
        if (!empty($collection->getAttachments()) || $collection->getAttachments() != '') {
            $attachments = explode(',', $collection->getAttachments());
            $attachmentCount = count($attachments);
            for ($i=0; $i < $attachmentCount; $i++) {
               
                $mediaPath = $imageUploadPath.$attachments[$i]; //set image path
                $body = $this->file->read($mediaPath);
                $mimetype = mime_content_type($mediaPath);
                $this->_transportBuilder->addAttachment(
                    $body,
                    $attachments[$i],
                    $mimetype
                );
            }
        }
        
        try {
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        $this->inlineTranslation->resume();
    }

    /**
     * Return template id
     *
     * @param string $xmlPath
     * @return void
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

   /**
    * Return store configuration value.
    *
    * @param string $path
    * @param int    $storeId
    *
    * @return mixed
    */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
