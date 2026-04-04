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
 * MpSellerBuyerCommunication Saveconversation controller.
 *
 */
namespace Webkul\MpSellerBuyerCommunication\Controller\AdminHtml\Query;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Webkul\MpSellerBuyerCommunication\Model\SellerBuyerCommunication;
use Webkul\MpSellerBuyerCommunication\Helper\Email;
use Webkul\MpSellerBuyerCommunication\Helper\Data;
use Magento\Framework\Filesystem\Driver\File;

class SaveReply extends Action
{
    public const XML_SENDER_TYPE = 1;

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
    protected $_customer;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * object of Filesystem
     * @var [type]
     */
    protected $_filesystem;

    /**
     * @var \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository
     */
    protected $_communicationRepository;

    /**
     * @var \Webkul\MpSellerBuyerCommunication\Helper\Data
     */
    protected $_commHelper;

    /**
     * @var \Webkul\MpSellerBuyerCommunication\Model\Conversation
     */
    protected $conversation;

    /**
     * @var SellerBuyerCommunication
     */
    protected $sellerBuyerCommunication;

    /**
     * @var Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $filesystemFile;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param Context $context
     * @param Customer $customer
     * @param FormKeyValidator $formKeyValidator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Registry $registry
     * @param PageFactory $resultPageFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Webkul\MpSellerBuyerCommunication\Model\ConversationRepository $conversationFactory
     * @param \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository $communicationRepository
     * @param \Webkul\MpSellerBuyerCommunication\Helper\Data $helper
     * @param \Webkul\MpSellerBuyerCommunication\Model\Conversation $conversation
     * @param SellerBuyerCommunication $sellerBuyerCommunication
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param TimezoneInterface $localeDate
     * @param Email $emailHelper
     * @param \Magento\Framework\Filesystem\Io\File $filesystemFile
     * @param File $fileDriver
     * @param DirectoryList $directoryList
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        Filesystem $filesystem,
        Context $context,
        Customer $customer,
        FormKeyValidator $formKeyValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Registry $registry,
        PageFactory $resultPageFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Webkul\MpSellerBuyerCommunication\Model\ConversationRepository $conversationFactory,
        \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository $communicationRepository,
        \Webkul\MpSellerBuyerCommunication\Helper\Data $helper,
        \Webkul\MpSellerBuyerCommunication\Model\Conversation $conversation,
        SellerBuyerCommunication $sellerBuyerCommunication,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\UrlInterface $urlInterface,
        TimezoneInterface $localeDate,
        Email $emailHelper,
        \Magento\Framework\Filesystem\Io\File $filesystemFile,
        File $fileDriver,
        DirectoryList $directoryList,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->_commHelper = $helper;
        $this->_customer = $customer;
        $this->resultPageFactory = $resultPageFactory;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_date = $date;
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_conversationFactory = $conversationFactory;
        $this->_communicationRepository = $communicationRepository;
        $this->urlInterface = $urlInterface;
        $this->localeDate = $localeDate;
        $this->conversation = $conversation;
        $this->sellerBuyerCommunication = $sellerBuyerCommunication;
        $this->emailHelper = $emailHelper;
        $this->mpHelper = $mpHelper;
        $this->filesystemFile = $filesystemFile;
        $this->fileDriver = $fileDriver;
        $this->registry =  $registry;
        $this->_directoryList =  $directoryList;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * Send mail to seller and save message in database
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        
        try {
            $data = $params['reply_form'];
            $currentDate = $this->localeDate->date()->format('Y-m-d H:i:s');
            $senderType = self::XML_SENDER_TYPE;
            if (isset($data['message'])) {
               
                $seller = $this->_customer->load($data['seller_id']);
                $sellerName = 'Administrator';
                $sellerEmail = $this->mpHelper->getAdminEmailId();
                $sellerEmail = $sellerEmail? $sellerEmail : $this->mpHelper->getDefaultTransEmailId();
                $sellerId =0;
                if ($data['seller_id'] != 0) {
                    $senderType = 2;
                }
                /*save new message in database*/
                $collectionData = $this->sellerBuyerCommunication->getCollection();
                $collectionData->addFieldToFilter(
                    'entity_id',
                    $data["entity_id"]
                );

                $convCollection  = $this->_conversationFactory->getCollectionByQueryId($data["entity_id"]);
                $convCollection->setOrder(
                    'created_at',
                    'desc'
                );

                $currentTimeZone = $this->_commHelper->getCurrentTimezone();
                $lastRepliedTime = $this->getLastRecentReplyOfSeller($convCollection);

                $customerRepliedTime = new \DateTime($lastRepliedTime);
                $customerRepliedTime->setTimezone(new \DateTimeZone($currentTimeZone));

                $currentReplyTime = new \DateTime($this->_date->gmtDate());

                $interval = date_diff($customerRepliedTime, $currentReplyTime);

                $responseTime = $interval->format('%h:%i:%s');
                
                if ($collectionData->getSize()) {
                    $this->changeQueryStatus($collectionData, $data);

                    $model = $this->conversation;
                    $model->setCommId($data["entity_id"]);
                    $model->setMessage($this->_commHelper->removeScriptFromData($data["message"]));
                    $model->setSender($sellerName);
                    $model->setSenderType($senderType);
                    $model->setCreatedAt($currentDate);
                    $model->setUpdatedAt($currentDate);
                    $model->setResponseTime($responseTime);
                    $lastConvId = $this->saveConversation($model);
                    $data['lastConvId'] = $lastConvId;
                    if (isset($data['img_attachment'])) {
                        $this->saveAttachments($data['img_attachment'], $data["entity_id"], $lastConvId);
                    }
                    $customerId = 0;
                    $subject = '';
                    foreach ($collectionData as $value) {
                        $subject = $value['subject'];
                        $customerId = $value['customer_id'];
                        $buyerEmail = $value['email_id'];
                    }

                    /*send mail to customer*/
                    $emailTemplateVariables = [];
                    $senderInfo = [];
                    $receiverInfo = [];
                    if ($customerId) {
                        $customerData = $this->_customer->load($customerId);

                        $buyerName = $customerData->getName();
                        $buyerEmail = $customerData->getEmail();
                    } else {
                        $buyerName = __('Guest');
                    }

                    $subject = (strlen($subject) > 50) ? substr($subject, 0, 50).'..' : $subject;
                    $emailTemplateVariables['myvar1'] = $buyerName;
                    $emailTemplateVariables['myvar2'] = $subject;
                    $emailTemplateVariables['myvar3'] = __('Seller');
                    $emailTemplateVariables['myvar4'] = $this->_commHelper->removeScriptFromData($data["message"]);
                    $emailTemplateVariables['myvar5'] = $this->urlInterface->getUrl(
                        'mpsellerbuyercommunication/customer/view',
                        ['_secure' => $this->getRequest()->isSecure(),'id' => $data["entity_id"]]
                    );
                    $senderInfo = [
                        'name' => $this->jsonHelper->jsonDecode($this->jsonHelper->jsonEncode($sellerName)),
                        'email' => $sellerEmail,
                    ];
                    $receiverInfo = [
                        'name' => $this->jsonHelper->jsonDecode($this->jsonHelper->jsonEncode($buyerName)),
                        'email' => $buyerEmail,
                    ];
                    $this->emailHelper->sendCommunicationEmail(
                        $data,
                        $emailTemplateVariables,
                        $senderInfo,
                        $receiverInfo
                    );
                    
                }
                if ($data['seller_id'] > 0) {
                    $seller = $this->_customer->load($data['seller_id']);
                    $sellerEmail = $seller->getEmail();
                    $sellerName = $seller->getFirstname().' '.$seller->getLastname();
                    $senderType = 2;
                  
                    $emailTemplateVariables = [];
                    $senderInfo = [];
                    $receiverInfo = [];
                    $adminStoreEmail = $this->mpHelper->getAdminEmailId();
                    $adminEmail=$adminStoreEmail? $adminStoreEmail:$this->mpHelper
                    ->getDefaultTransEmailId();
                    $adminUsername = __('Admin');
                    $subject = (strlen($subject) > 50) ? substr($subject, 0, 50).'..' : $subject;
                    $emailTemplateVariables['myvar1'] = $sellerName;
                    $emailTemplateVariables['myvar2'] = $subject;
                    $emailTemplateVariables['myvar3'] = __('Admin');
                    $emailTemplateVariables['myvar4'] = $this->_commHelper->removeScriptFromData($data['message']);
                    $emailTemplateVariables['myvar5'] = $this->urlInterface->getUrl(
                        'mpsellerbuyercommunication/seller/view',
                        ['_secure' => $this->getRequest()->isSecure(),'id' => $data["entity_id"]]
                    );
                    $senderInfo = [
                        'name' => $this->jsonHelper->jsonDecode($this->jsonHelper->jsonEncode($adminUsername)),
                        'email' => $adminEmail,
                    ];
                    $receiverInfo = [
                        'name' => $this->jsonHelper->jsonDecode($this->jsonHelper->jsonEncode($sellerName)),
                        'email' => $sellerEmail,
                    ];
                    $this->emailHelper->sendCommunicationEmailToSeller(
                        $data,
                        $emailTemplateVariables,
                        $senderInfo,
                        $receiverInfo
                    );
                }
                $this->messageManager->addSuccess(__('The message has been sent successfully.'));
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/view',
                    ['comm_id' => $data["entity_id"], '_secure'=>$this->getRequest()->isSecure()]
                );
            } else {
                $this->messageManager->addError(__('Message not found'));
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/reply',
                    ['comm_id' => $data["entity_id"], '_secure'=>$this->getRequest()->isSecure()]
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/reply',
                ['comm_id' => $data["entity_id"], '_secure'=>$this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Save Conversation
     *
     * @param collection $model
     * @return void
     */
    public function saveConversation($model)
    {
        return $model->save()->getId();
    }

    /**
     * Get recent replay time of customer
     *
     * @param  object $collection
     * @return timestamp
     */
    private function getLastRecentReplyOfSeller($collection)
    {
        $lastRepliedTime = null;
        foreach ($collection as $record) {
            if (!$record->getSenderType()) {
                $lastRepliedTime = $record->getUpdatedAt();
            } else {
                if (!empty($lastRepliedTime)) {
                    break;
                }
            }
        }
        return $lastRepliedTime;
    }

    /**
     * Change query status
     *
     * @param  object $collection
     * @param  array $data
     */
    private function changeQueryStatus($collection, $data)
    {
        foreach ($collection as $key => $sellerData) {
            $sellerData->setQueryStatus($data['query_status']);
            $this->saveQueryStatus($sellerData);
        }
    }

    /**
     * Save Query Status
     *
     * @param array $sellerData
     * @return void
     */
    public function saveQueryStatus($sellerData)
    {
        $sellerData->save();
    }

    /**
     * Save attachment
     *
     * @param  array $attachData
     * @param  int $commentId
     * @param  int $lastConvId
     */
    private function saveAttachments($attachData, $commentId, $lastConvId)
    {
        $communicationMediaPath = 'sellerbuyercommunication/'.$commentId.'/'.$lastConvId.'/';
        if (count($attachData)) {
            foreach ($attachData as $attachment) {
                $uploadedFile = explode('media/', $attachment['url']);
                if (isset($uploadedFile[1])) {
                    $path = $communicationMediaPath.$attachment['file'];
                    $newPath = $this->fileCheck($path);
                    $imageUploadPath = $this->_filesystem->getDirectoryRead(
                        DirectoryList::MEDIA
                    )->getAbsolutePath($communicationMediaPath);
                    if (!$this->fileDriver->isDirectory($imageUploadPath)) {
                        $this->filesystemFile->mkdir($imageUploadPath, 0777, true);
                    }
                    $mediaPath = $this->_directoryList->getPath('media');
                    $directory = $this->_filesystem
                        ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::PUB);
                    $driver = $directory->getDriver();
                    $driver->rename($mediaPath.'/'.$uploadedFile[1], $mediaPath.'/'.$newPath);
                    $newPath = str_replace($communicationMediaPath, '', $newPath);
                    $imageData[] = $newPath;
                }
            }

            if (!empty($imageData)) {
                $commConnection = $this->_communicationRepository
                                ->getCollectionByEntityId($commentId);
                if (!$commConnection->getStatus()) {
                    $commConnection->setAttachmentStatus('1')
                        ->setEntityId($commentId)->save();
                }
                $collection = $this->_conversationFactory->getCollectionByEntityId($lastConvId);
                $collection->setAttachments(implode(',', $imageData))
                    ->setEntityId($collection->getEntityId())->save();
            }
        }
    }

    /**
     * Check if file exists or not
     *
     * @param [type] $path
     * @return void
     */
    public function fileCheck($path)
    {
        $pre = 0;
        $basePath = explode('.', $path)[0];
        $newPath = $path;
        while ($this->fileDriver->isExists($path)) {
            $newPath = explode('.', $path);
            $pre++;
            $newPath[0] = $basePath.'_'.$pre;
            $newPath = implode('.', $newPath);
        }
        return $newPath;
    }

   /**
    * Save File
    *
    * @param string $uploader
    * @param string $imageUploadPath
    * @return void
    */
    public function savefile($uploader, $imageUploadPath)
    {
        return $uploader->save($imageUploadPath);
    }
}
