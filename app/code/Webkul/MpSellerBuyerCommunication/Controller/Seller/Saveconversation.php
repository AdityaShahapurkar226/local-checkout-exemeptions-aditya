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
namespace Webkul\MpSellerBuyerCommunication\Controller\Seller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Webkul\MpSellerBuyerCommunication\Model\SellerBuyerCommunication;
use Webkul\MpSellerBuyerCommunication\Helper\Email;
use Webkul\MpSellerBuyerCommunication\Helper\Data;
use Magento\Framework\Filesystem\Driver\File;

class Saveconversation extends Action
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
    protected $_helper;

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
     * @param Session $customerSession
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
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        Filesystem $filesystem,
        Context $context,
        Session $customerSession,
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
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->_helper = $helper;
        $this->_customerSession = $customerSession;
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
        $this->jsonHelper = $jsonHelper ?: \Magento\Framework\App\ObjectManager::getInstance()
        ->get(\Magento\Framework\Json\Helper\Data::class);
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
        try {
            $data = $this->getRequest()->getParams();
            $currentDate = $this->localeDate->date()->format('Y-m-d H:i:s');

            if (isset($data['message'])) {
                $sendEmailFromSeller = $this->_helper->getSendMailFromSellerId();
                $sellerId = $this->_helper->getCurrentCustomer();
                $sellerName = $this->_customerSession->getCustomer()->getName();
                $sellerEmail = $this->_customerSession->getCustomer()->getEmail();
                $adminStoreId = $this->mpHelper->getAdminEmailId();
                $adminEmail=$adminStoreId? $adminStoreId:$this->mpHelper->getDefaultTransEmailId();
                $adminUsername = __('Admin');
                /*save new message in database*/
                $collectionData = $this->sellerBuyerCommunication->getCollection();
                $collectionData->addFieldToFilter(
                    'seller_id',
                    $sellerId
                )->addFieldToFilter(
                    'entity_id',
                    $data["comm_id"]
                );

                $convCollection  = $this->_conversationFactory->getCollectionByQueryId($data["comm_id"]);
                $convCollection->setOrder(
                    'created_at',
                    'desc'
                );
                $currentTimeZone = $this->_helper->getCurrentTimezone();
                $lastRepliedTime = $this->getLastRecentReplyOfSeller($convCollection);

                $customerRepliedTime = new \DateTime($lastRepliedTime);
                $customerRepliedTime->setTimezone(new \DateTimeZone($currentTimeZone));

                $currentReplyTime = new \DateTime($this->_date->gmtDate());

                $interval = date_diff($customerRepliedTime, $currentReplyTime);

                $responseTime = $interval->format('%h:%i:%s');

                if ($collectionData->getSize()) {
                    $this->changeQueryStatus($collectionData, $data);

                    $model = $this->conversation;
                    $model->setCommId($data["comm_id"]);
                    $model->setMessage($this->_helper->removeScriptFromData($data["message"]));
                    $model->setSender($sellerName);
                    $model->setSenderType(self::XML_SENDER_TYPE);
                    $model->setCreatedAt($currentDate);
                    $model->setUpdatedAt($currentDate);
                    $model->setResponseTime($responseTime);
                    $lastConvId = $this->saveConversation($model);
                    $data['lastConvId'] = $lastConvId;
                    $this->saveAttachments($data['attach_count'], $data["comm_id"], $lastConvId);
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
                    if ($sendEmailFromSeller == '1') {
                        $senderName = $this->jsonHelper->jsonDecode($this->jsonHelper->jsonEncode($sellerName));
                        $senderEmail = $sellerEmail;
                    } else {
                        $senderName = $this->jsonHelper->jsonDecode($this->jsonHelper->jsonEncode($adminUsername));
                        $senderEmail = $adminEmail;
                    }
                    $subject = (strlen($subject) > 50) ? substr($subject, 0, 50).'..' : $subject;
                    $emailTemplateVariables['myvar1'] = $buyerName;
                    $emailTemplateVariables['myvar2'] = $subject;
                    $emailTemplateVariables['myvar3'] = __('Seller');
                    $emailTemplateVariables['myvar4'] = $this->_helper->removeScriptFromData($data["message"]);
                    $emailTemplateVariables['myvar5'] = $this->urlInterface->getUrl(
                        'mpsellerbuyercommunication/customer/view',
                        ['_secure' => $this->getRequest()->isSecure(),'id' => $data["comm_id"]]
                    );
                    $senderInfo = [
                        'name' => $senderName,
                        'email' => $senderEmail,
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

                    /*send notification mail to admin if enabled*/
                    if ($this->_helper->getAdminNotificationStatus()==1
                    ) {
                        $senderInfo = [];
                        $receiverInfo = [];
                        $adminStoreId = $this->mpHelper->getAdminEmailId();
                        $adminEmail=$adminStoreId? $adminStoreId:$this->mpHelper->getDefaultTransEmailId();
                        $adminUsername = __('Admin');
                        $emailTemplateVariables['myvar1'] = $adminUsername;
                        $senderInfo = [
                            'name' => $sellerName,
                            'email' => $sellerEmail,
                        ];
                        $receiverInfo = [
                            'name' => $this->jsonHelper->jsonDecode($this->jsonHelper->jsonEncode($adminUsername)),
                            'email' => $adminEmail,
                        ];
                       
                        $this->emailHelper->sendCommunicationEmailToAdmin(
                            $data,
                            $emailTemplateVariables,
                            $senderInfo,
                            $receiverInfo
                        );
                    }
                    
                }
                $this->messageManager->addSuccess(__('The message has been sent successfully.'));
                return $this->resultRedirectFactory->create()->setPath(
                    'mpsellerbuyercommunication/seller/view',
                    ['id'=>$data["comm_id"], '_secure'=>$this->getRequest()->isSecure()]
                );
            } else {
                $this->messageManager->addError(__('The message can not be empty'));
                return $this->resultRedirectFactory->create()->setPath(
                    'mpsellerbuyercommunication/seller/view',
                    ['id'=>$data["comm_id"], '_secure'=>$this->getRequest()->isSecure()]
                );
            }

        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath(
                'mpsellerbuyercommunication/seller/view',
                ['id'=>$data["comm_id"], '_secure'=>$this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Save Conversation
     *
     * @param object $model
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
     * @param object $sellerData
     * @return void
     */
    public function saveQueryStatus($sellerData)
    {
        $sellerData->save();
    }

    /**
     * Save attachment
     *
     * @param  int $attachCount
     * @param  int $commentId
     * @param  int $lastConvId
     */
    private function saveAttachments($attachCount, $commentId, $lastConvId)
    {
        $files = $this->getRequest()->getFiles()->toArray();
        $imageData = [];

        $imageUploadPath = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath('sellerbuyercommunication/'.$commentId.'/'.$lastConvId.'/');
        if (!$this->fileDriver->isDirectory($imageUploadPath)) {
            $this->filesystemFile->mkdir($imageUploadPath, 0755, true);
        }
        for ($i=1; $i <= $attachCount; $i++) {
            if ($files['img_attachment_'.$i]['error']!=4) {
                try {
                    $uploader = $this->_fileUploaderFactory->create(['fileId' => 'img_attachment_'.$i]);
                    $uploader->setAllowedExtensions(
                        ['jpeg', 'jpg', 'png', 'gif', 'zip', 'doc', 'pdf', 'rar', 'xls', 'xlsx', 'csv']
                    );
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);
                    $resultLogo = $this->savefile($uploader, $imageUploadPath);

                    if ($resultLogo['file']) {
                        $imageData[] = $resultLogo['file'];
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }
        if (!empty($imageData)) {
            $commConnection  = $this->_communicationRepository
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

    /**
     * Save File
     *
     * @param object $uploader
     * @param string $imageUploadPath
     * @return void
     */
    public function savefile($uploader, $imageUploadPath)
    {
        return $uploader->save($imageUploadPath);
    }
}
