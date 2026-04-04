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
namespace Webkul\MpSellerBuyerCommunication\Controller\Seller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Filesystem\Driver\File;

class SaveFiles extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * object of Filesystem
     * @var [type]
     */
    protected $_filesystem;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository
     */
    protected $_communicationRepository;

    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\MpSellerBuyerCommunication\Model\ConversationRepository $conversationFactory
     * @param \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository $communicationRepository
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param Data $data
     * @param \Magento\Framework\Filesystem\Io\File $filesystemFile
     * @param File $fileDriver
     * @param \Webkul\MpSellerBuyerCommunication\Logger\Logger $logger
     */
    public function __construct(
        Filesystem $filesystem,
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpSellerBuyerCommunication\Model\ConversationRepository $conversationFactory,
        \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository $communicationRepository,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        Data $data,
        \Magento\Framework\Filesystem\Io\File $filesystemFile,
        File $fileDriver,
        \Webkul\MpSellerBuyerCommunication\Logger\Logger $logger
    ) {
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
        $this->_conversationFactory = $conversationFactory;
        $this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_communicationRepository = $communicationRepository;
        $this->data = $data;
        $this->filesystemFile = $filesystemFile;
        $this->fileDriver = $fileDriver;
        $this->logger = $logger;
    }

    /**
     * My Communication History Page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();

        $data = $this->getRequest()->getParams();
        $collection = $this->_conversationFactory->getCollectionByEntityId($data['id']);

        $imageUploadPath = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath('sellerbuyercommunication/'.$collection->getCommId().'/'.$collection->getEntityId().'/');
        if (!$this->fileDriver->isDirectory($imageUploadPath)) {
            $this->filesystemFile->mkdir($imageUploadPath, 0755, true);
        }

        $imageData = [];
        for ($i=0; $i < $data['itr']; $i++) {
            try {
                $fileData = $this->getRequest()->getFiles();
                $fileData = $fileData['attachment_'.$i];
                $mimetype = mime_content_type($fileData['tmp_name']);
                if ($mimetype != $fileData['type']) {
                    $this->messageManager->addError(__('%1 is not a valid file.', $fileData['name']));
                    continue;
                }

                $uploader = $this->_fileUploaderFactory->create(['fileId' => 'attachment_'.$i]);
                $uploader->setAllowedExtensions(['jpeg', 'jpg', 'png', 'zip', 'gif', 'doc', 'pdf', 'rar']);

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
        if (!empty($imageData)) {
            $commConnection  = $this->_communicationRepository
                            ->getCollectionByEntityId($collection->getCommId());
            if ($commConnection->getEntityId()) {
                $commConnection->setAttachmentStatus('1')
                    ->setEntityId($collection->getCommId())->save();
            }
            $collection->setAttachments(implode(',', $imageData))
                ->setEntityId($collection->getEntityId())->save();
        }

        $this->getResponse()->representJson(
            $this->data->jsonEncode('true')
        );
    }
     /**
      * Save Attachment Files
      *
      * @param array $data
      * @return void
      */
    public function saveAttachmentFiles($data)
    {
        $collection = $this->_conversationFactory->getCollectionByEntityId($data['id']);

        $imageUploadPath = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath('sellerbuyercommunication/'.$collection->getCommId().'/'.$collection->getEntityId().'/');
        if (!$this->fileDriver->isDirectory($imageUploadPath)) {
            $this->filesystemFile->mkdir($imageUploadPath, 0755, true);
        }
        $imageData = [];
        $attachment = count($data['fileData']['img_attachment']);
        for ($i=0; $i < $attachment; $i++) {
            if ($data['fileData']['img_attachment'][$i]['error']!=4) {
                try {
                    $fileData = $data['fileData']['img_attachment'];
                    $fileData = $fileData[$i];
                    $mimetype = mime_content_type($fileData['tmp_name']);
                    if ($mimetype != $fileData['type']) {
                    
                        $this->messageManager->addError(__('%1 is not a valid file.', $fileData['name']));
                        continue;
                    }
                    $uploader = $this->_fileUploaderFactory->create(['fileId' => 'img_attachment['.$i.']']);
                    $uploader->setAllowedExtensions(['jpeg', 'jpg', 'png', 'zip', 'gif', 'doc', 'pdf', 'rar']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);
                    $resultLogo = $this->saveImageUpload($uploader, $imageUploadPath);
                    if ($resultLogo['file']) {
                        $imageData[] = $resultLogo['file'];
                    }
                } catch (\Exception $e) {
                    $this->logger->info(json_encode($e->getMessage()));
                }
            }
        }
        if (!empty($imageData)) {
            $commConnection  = $this->_communicationRepository
                            ->getCollectionByEntityId($collection->getCommId());
            if ($commConnection->getEntityId()) {
                $commConnection->setAttachmentStatus('1')
                    ->setEntityId($collection->getCommId())->save();
            }
            $collection->setAttachments(implode(',', $imageData))
                ->setEntityId($collection->getEntityId())->save();
        }
    }
    /**
     * Save Image
     *
     * @param object $uploader
     * @param string $imageUploadPath
     * @return void
     */
    public function saveImageUpload($uploader, $imageUploadPath)
    {
        return $uploader->save($imageUploadPath);
    }
}
