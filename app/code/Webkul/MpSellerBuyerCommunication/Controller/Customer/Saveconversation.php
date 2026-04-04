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
 * MpSellerBuyerCommunication Customer Saveconversation controller.
 *
 */
namespace Webkul\MpSellerBuyerCommunication\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

use Magento\Customer\Model\Session;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Webkul\MpSellerBuyerCommunication\Helper\Email;
use Magento\Framework\Filesystem\Driver\File;

class Saveconversation extends Action
{
    public const XML_SENDER_TYPE = 0;

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
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository
     */
    protected $_communicationRepository;

    /**
     * @var Webkul\MpSellerBuyerCommunication\Model\SellerBuyerCommunication
     */
    protected $sellerBuyer;

    /**
     * @var \Webkul\MpSellerBuyerCommunication\Model\Conversation
     */
    protected $sellerConversation;

    /**
     * @var Webkul\MpSellerBuyerCommunication\Helper\Email
     */
    protected $email;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param Context $context
     * @param Session $customerSession
     * @param Customer $customer
     * @param FormKeyValidator $formKeyValidator
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param PageFactory $resultPageFactory
     * @param \Webkul\MpSellerBuyerCommunication\Model\ConversationRepository $conversationFactory
     * @param \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository $communicationRepository
     * @param TimezoneInterface $localeDate
     * @param \Webkul\MpSellerBuyerCommunication\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Webkul\MpSellerBuyerCommunication\Model\SellerBuyerCommunication $sellerBuyer
     * @param \Webkul\MpSellerBuyerCommunication\Model\Conversation $sellerConversation
     * @param \Webkul\Marketplace\Helper\Data $data
     * @param Email $email
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
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        PageFactory $resultPageFactory,
        \Webkul\MpSellerBuyerCommunication\Model\ConversationRepository $conversationFactory,
        \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository $communicationRepository,
        TimezoneInterface $localeDate,
        \Webkul\MpSellerBuyerCommunication\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlInterface,
        \Psr\Log\LoggerInterface $logger,
        \Webkul\MpSellerBuyerCommunication\Model\SellerBuyerCommunication $sellerBuyer,
        \Webkul\MpSellerBuyerCommunication\Model\Conversation $sellerConversation,
        \Webkul\Marketplace\Helper\Data $data,
        Email $email,
        \Magento\Framework\Filesystem\Io\File $filesystemFile,
        File $fileDriver,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->_customerSession = $customerSession;
        $this->_customer = $customer;
        $this->resultPageFactory = $resultPageFactory;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_date = $date;
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_conversationFactory = $conversationFactory;
        $this->_communicationRepository = $communicationRepository;
        $this->localeDate = $localeDate;
        $this->_helper = $helper;
        $this->urlInterface = $urlInterface;
        $this->_logger = $logger;
        $this->sellerBuyer = $sellerBuyer;
        $this->sellerConversation = $sellerConversation;
        $this->email = $email;
        $this->data = $data;
        $this->filesystemFile = $filesystemFile;
        $this->fileDriver = $fileDriver;
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
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                return $this->resultRedirectFactory->create()
                ->setPath(
                    'mpsellerbuyercommunication/customer/view',
                    ['id'=>$data["comm_id"], '_secure'=>$this->getRequest()->isSecure()]
                );
            }
            if ($data['message']) {
                $sendEmailFromSeller = $this->_helper->getSendMailFromSellerId();
                $customerId = $this->_helper->getCurrentCustomer();
                $customerName = $this->_customerSession->getCustomer()->getName();
                $customerEmail = $this->_customerSession->getCustomer()->getEmail();

                /*save new message in database*/
                $collectionData = $this->sellerBuyer
                ->getCollection()
                ->addFieldToFilter(
                    'customer_id',
                    $customerId
                )->addFieldToFilter(
                    'entity_id',
                    $data["comm_id"]
                );

                if ($collectionData->getSize()) {
                    $this->changeQueryStatus($collectionData, $data);

                    /*save details in database*/
                    $model = $this->sellerConversation;
                    $model->setCommId($data["comm_id"]);
                    $model->setMessage($this->_helper->removeScriptFromData($data['message']));
                    $model->setSender($customerName);
                    $model->setSenderType(self::XML_SENDER_TYPE);
                    $model->setCreatedAt($currentDate);
                    $model->setUpdatedAt($currentDate);
                    $lastConvId = $model->save()->getId();
                    $data['lastConvId'] = $lastConvId;
                    $this->saveAttachments($data['attach_count'], $data["comm_id"], $lastConvId);
                    $sellerId = 0;
                    $subject = '';
                    foreach ($collectionData as $value) {
                        $subject = $value['subject'];
                        $sellerId = $value['seller_id'];
                    }
                    $adminStoreEmail = $this->data->getAdminEmailId();
                    $adminEmail=$adminStoreEmail? $adminStoreEmail:$this->data
                    ->getDefaultTransEmailId();
                    $adminUsername = __('Admin');
                    /*send mail to seller*/
                    $emailTemplateVariables = [];
                    $senderInfo = [];
                    $receiverInfo = [];
                    if ($sellerId) {
                        $sellerData = $this->_customer->load($sellerId);

                        $sellerName = $sellerData->getName();
                        $sellerEmail = $sellerData->getEmail();
                    } else {
                        $sellerName = 'Administrator';
                        $adminStoreEmail = $this->data->getAdminEmailId();
                        $sellerEmail=$adminStoreEmail? $adminStoreEmail:$this->data
                        ->getDefaultTransEmailId();
                    }
                    if ($sendEmailFromSeller == '1') {
                        $senderName = $this->jsonHelper->jsonDecode($this->jsonHelper->jsonEncode($customerName));
                        $senderEmail = $customerEmail;
                    } else {
                        $senderName = $this->jsonHelper->jsonDecode($this->jsonHelper->jsonEncode($adminUsername));
                        $senderEmail = $adminEmail;
                    }
                    $subject = (strlen($subject) > 50) ? substr($subject, 0, 50).'..' : $subject;
                    $emailTemplateVariables['myvar1'] = $sellerName;
                    $emailTemplateVariables['myvar2'] = $subject;
                    $emailTemplateVariables['myvar3'] = __('Customer');
                    $emailTemplateVariables['myvar4'] = $this->_helper->removeScriptFromData($data['message']);
                    $emailTemplateVariables['myvar5'] = $this->urlInterface->getUrl(
                        'mpsellerbuyercommunication/seller/view',
                        ['_secure' => $this->getRequest()->isSecure(),'id' => $data["comm_id"]]
                    );
                    $senderInfo = [
                        'name' => $senderName,
                        'email' => $senderEmail,
                    ];
                    $receiverInfo = [
                        'name' => $this->jsonHelper->jsonDecode($this->jsonHelper->jsonEncode($sellerName)),
                        'email' => $sellerEmail,
                    ];
                    $this->email->sendCommunicationEmail(
                        $data,
                        $emailTemplateVariables,
                        $senderInfo,
                        $receiverInfo
                    );

                    /*send notification mail to admin if enabled*/
                    if ($sellerId) {
                        if ($this->_helper->getAdminNotificationStatus()==1) {
                            $senderInfo = [];
                            $receiverInfo = [];
                            $adminStoreEmail = $this->data->getAdminEmailId();
                            $adminEmail=$adminStoreEmail? $adminStoreEmail:$this->data
                            ->getDefaultTransEmailId();
                            $adminUsername = __('Admin');
                            $emailTemplateVariables['myvar1'] = $adminUsername;
                            $senderInfo = [
                                'name' => $customerName,
                                'email' => $customerEmail,
                            ];
                            $receiverInfo = [
                                'name' => $this->jsonHelper->jsonDecode($this->jsonHelper->jsonEncode($adminUsername)),
                                'email' => $adminEmail,
                            ];

                            $this->email
                            ->sendCommunicationEmailToAdmin($data, $emailTemplateVariables, $senderInfo, $receiverInfo);
                        }
                    }
                    
                }
            }
            $this->messageManager->addSuccess(__('The message has been sent successfully.'));
            return $this->resultRedirectFactory->create()
            ->setPath(
                'mpsellerbuyercommunication/customer/view',
                [
                    'id'=>$data["comm_id"],
                    '_secure'=>$this->getRequest()->isSecure()
                ]
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()
            ->setPath(
                'mpsellerbuyercommunication/customer/view',
                [
                    'id'=>$data["comm_id"],
                    '_secure'=>$this->getRequest()->isSecure()
                ]
            );
        }
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
            $this->saveSellerStatus($sellerData);
        }
    }

    /**
     * Save Seller Status
     *
     * @param object $sellerData
     * @return void
     */
    public function saveSellerStatus($sellerData)
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
                        ['jpeg', 'jpg', 'png', 'gif', 'zip', 'doc', 'pdf', 'rar', 'csv', 'xls', 'xlsx']
                    );
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);
                    $resultLogo = $this->saveImageUpload($uploader, $imageUploadPath);

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
     * Save Image
     *
     * @param string $uploader
     * @param string $imageUploadPath
     * @return void
     */
    public function saveImageUpload($uploader, $imageUploadPath)
    {
        return $uploader->save($imageUploadPath);
    }
}
