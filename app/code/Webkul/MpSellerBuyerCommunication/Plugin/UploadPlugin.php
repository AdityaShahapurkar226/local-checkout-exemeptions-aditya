<?php

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerBuyerCommunication
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpSellerBuyerCommunication\Plugin;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\Marketplace\Api\Data\WysiwygImageInterfaceFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;

/**
 * Marketplace Wysiwyg Image Upload controller.
 */
class UploadPlugin extends \Webkul\Marketplace\Controller\Wysiwyg\Gallery\Upload
{
    /**
     * Execute
     *
     * @return void
     */
    public function execute()
    {
        $helper = $this->mpHelper;
        $isPartner = $helper->isSeller();
        $sellerId = $helper->getCustomerId();
        try {
            $target = $this->mediaDirectory->getAbsolutePath(
                'tmp/desc'
            );
            $fileUploader = $this->fileUploaderFactory->create(
                ['fileId' => 'image']
            );
            $fileUploader->setAllowedExtensions(
                ['gif', 'jpg', 'png', 'jpeg']
            );
            $fileUploader->setFilesDispersion(true);
            $fileUploader->setAllowRenameFiles(true);
            $resultData = $fileUploader->save($target);
            unset($resultData['tmp_name']);
            unset($resultData['path']);
            $resultData['url'] = $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . 'tmp/desc' . '/' . ltrim(str_replace('\\', '/', $resultData['file']), '/');
            $resultData['file'] = $resultData['file'] . '.tmp';
            if ($isPartner == 1) {
                $checkVal = $this->saveImageDesc($sellerId, $resultData['url'], $resultData['file']);
            }
            $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode($resultData)
            );
        } catch (\Exception $e) {
            $helper->logDataInLogger(
                "Controller_Wysiwyg_Gallery_Upload execute : " . $e->getMessage()
            );
            $this->getResponse()->representJson(
                $this->jsonHelper->jsonEncode(
                    [
                        'error' => $e->getMessage(),
                        'errorcode' => $e->getCode(),
                    ]
                )
            );
        }
    }
}
