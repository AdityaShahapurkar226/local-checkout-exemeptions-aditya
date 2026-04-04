<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpMassUpload
 * @author Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\MpMassUpload\Model;

use Webkul\MpMassUpload\Api\Data\FileUploaderRequestInterface;
use Webkul\MpMassUpload\Api\FileUploaderInterface;

/**
 * FileUploader Class Curd
 */
class FileUploader implements FileUploaderInterface
{

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Webkul\MpMassUpload\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Webkul\MpMassUpload\Helper\Data $helper
    ) {
        $this->request = $request;
        $this->uploaderFactory = $uploaderFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->helper = $helper;
    }

    /**
     *  Save File Upload
     *
     * @param \Webkul\MpMassUpload\Api\Data\FileUploaderRequestInterface $fileData
     * @return \Webkul\MpMassUpload\Api\Data\FileUploaderRequestInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function fileUpload(FileUploaderRequestInterface $fileData)
    {
        $path = $fileData->getPath();
        $images = (array)$this->request->getFiles();
        if (isset($images['fileData']['file'])) {
            
            $response = $this->saveFileToTmpDir($images['fileData']['file'], $path);
            $fileData->setFile($response['file']);
            $fileData->setPath($response['path']);
        }
        return $fileData;
    }

    /**
     * Save File To Temp Folder
     *
     * @param string $fileId
     * @param string $path
     * @return array
     */
    public function saveFileToTmpDir($fileId, $path)
    {
        $result = [];
        $baseTmpPath = $path.'/tmp';
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
            $uploader->setAllowRenameFiles(true);

            $result = $uploader->save($this->mediaDirectory->getAbsolutePath($baseTmpPath));

            if (!$result) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('File can not be saved to the destination folder.')
                );
            }
        
            $result['path'] = str_replace('\\', '/', $result['path']);
            $result['name'] = $result['file'];
            return $result;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }
}
