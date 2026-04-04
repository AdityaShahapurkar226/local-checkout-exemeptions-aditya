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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class SaveFiles extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $_filesystemFile;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem\Io\File $filesystemFile
     * @param \Webkul\MpSellerBuyerCommunication\Model\ConversationRepository $conversationFactory
     * @param \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository $communicationRepository
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Webkul\MpSellerBuyerCommunication\Logger\Logger $logger
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem\Io\File $filesystemFile,
        \Webkul\MpSellerBuyerCommunication\Model\ConversationRepository $conversationFactory,
        \Webkul\MpSellerBuyerCommunication\Model\CommunicationRepository $communicationRepository,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Webkul\MpSellerBuyerCommunication\Logger\Logger $logger
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->storeManager = $storeManager;
        $this->_filesystemFile = $filesystemFile;
        $this->_filesystem = $filesystem;
        $this->_conversationFactory = $conversationFactory;
        $this->_communicationRepository = $communicationRepository;
        $this->fileDriver = $fileDriver;
        $this->logger = $logger;
    }
    /**
     * Execute
     *
     * @return void
     */
    public function execute()
    {
        $result = [];
        if ($this->getRequest()->isPost()) {
            try {
                $fields = $this->getRequest()->getParams();
                $imageId = $this->_request->getParam('param_name', 'reply_form[img_attachment]');
                
                $AttachmentDirPath = $this->_mediaDirectory->getAbsolutePath(
                    'sellerbuyercommunication/'.$fields['comm_id'].'/'.$fields['conv_id']
                );
                
                if (!$this->_filesystemFile->fileExists($AttachmentDirPath)) {
                    $this->_filesystemFile->mkdir($AttachmentDirPath, 0777, true);
                }
                $baseTmpPath = 'sellerbuyercommunication/'.$fields['comm_id'].'/'.$fields['conv_id'];
                $target = $this->_mediaDirectory->getAbsolutePath($baseTmpPath);
                try {
                    /**
                     * @var $uploader \Magento\MediaStorage\Model\File\Uploader
                     */
                    $uploader = $this->_fileUploaderFactory->create(
                        ['fileId' => $imageId]
                    );
                    $uploader->setAllowedExtensions(
                        ['jpeg', 'jpg', 'png', 'gif', 'zip', 'doc', 'pdf', 'rar', 'xls', 'xlsx', 'csv']
                    );
                    $uploader->setAllowRenameFiles(true);
                    $result = $uploader->save($target);
                    if (!$result) {
                        $result = [
                            'error' => __('File can not be saved to the destination folder.'),
                            'errorcode' => ''
                        ];
                    }
                    if (isset($result['file'])) {
                        try {
                            $result['tmp_name'] = str_replace('\\', '/', $result['tmp_name']);
                            $result['path'] = str_replace('\\', '/', $result['path']);
                            $result['url'] = $this->storeManager
                                ->getStore()
                                ->getBaseUrl(
                                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                                ) . $this->getFilePath($baseTmpPath, $result['file']);
                            $result['name'] = $result['file'];
                            $result['id'] = $result['file'];
                        } catch (\Exception $e) {
                            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
                        }
                    }

                    $result['cookie'] = [
                        'name' => $this->_getSession()->getName(),
                        'value' => $this->_getSession()->getSessionId(),
                        'lifetime' => $this->_getSession()->getCookieLifetime(),
                        'path' => $this->_getSession()->getCookiePath(),
                        'domain' => $this->_getSession()->getCookieDomain(),
                    ];
                } catch (\Exception $e) {
                    $result = ['error' => $e->getMessage().'cdgvrf', 'errorcode' => $e->getCode()];
                }
            } catch (\Exception $e) {
                $result = ['error' => $e->getMessage().'vsfe', 'errorcode' => $e->getCode()];
            }
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
    /**
     * Save Attachemtn Files
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
            $this->_filesystemFile->mkdir($imageUploadPath, 0755, true);
        }
        $imageData = [];
        $attachmentCount = count($data['fileData']['img_attachment']);
        for ($i=0; $i < $attachmentCount; $i++) {
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
                    $this->messageManager->addError($e->getMessage());
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
     * @param string $uploader
     * @param string $imageUploadPath
     * @return void
     */
    public function saveImageUpload($uploader, $imageUploadPath)
    {
        return $uploader->save($imageUploadPath);
    }

    /**
     * Retrieve path
     *
     * @param string $path
     * @param string $imageName
     *
     * @return string
     */
    public function getFilePath($path, $imageName)
    {
        return rtrim($path, '/') . '/' . ltrim($imageName, '/');
    }
}
