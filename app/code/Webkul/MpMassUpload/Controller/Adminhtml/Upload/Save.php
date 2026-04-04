<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMassUpload\Controller\Adminhtml\Upload;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Save extends \Webkul\MpMassUpload\Controller\Adminhtml\Upload
{
    /**
     * @var \Webkul\MpMassUpload\Helper\Data
     */
    protected $_massUploadHelper;

   /**
    * @param Context $context
    * @param \Webkul\MpMassUpload\Helper\Data $massUploadHelper
    */
    public function __construct(
        Context $context,
        \Webkul\MpMassUpload\Helper\Data $massUploadHelper
    ) {
        $this->_massUploadHelper = $massUploadHelper;
        parent::__construct($context);
        $validateData = $this->validateUploadedFiles();
        if ($validateData[0]['error']) {
            return $this->resultRedirectFactory->create()->setPath('admin/dashboard/index');
        }
    }

    /**
     * Return validate uploaded files
     *
     * @return array
     */
    protected function validateUploadedFiles()
    {
        $noValidate="";
        $files = $this->getRequest()->getFiles();
        if ($files['massupload_image']['name']=="") {
            $noValidate='image';
        }
        $helper = $this->_massUploadHelper;
        $validateData = $helper->validateUploadedFiles($noValidate);
        if ($validateData['error']) {
            $this->messageManager->addError(__($validateData['msg']));
            return [$validateData,$noValidate];
        } else {
            return [$validateData,$noValidate];
        }
    }

    /**
     * Return execute
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $helper = $this->_massUploadHelper;
        $validateData = $this->validateUploadedFiles();
        if (!$validateData[0]['error']) {
            $productType = $validateData[0]['type'];
            $fileName = $validateData[0]['csv'];
            $fileData = $validateData[0]['csv_data'];
            $result = $helper->saveProfileData(
                $productType,
                $fileName,
                $fileData,
                $validateData[0]['extension']
            );
            $uploadCsv = $helper->uploadCsv($result, $validateData[0]['extension'], $fileName);
            if ($uploadCsv['error']) {
                $this->messageManager->addError(__($uploadCsv['msg']));
                return $this->resultRedirectFactory->create()->setPath('*/upload/index');
            }
            if (empty($validateData[1])) {
                $uploadZip = $helper->uploadZip($result, $fileData);
                if ($uploadZip['error']) {
                    $this->messageManager->addError(__($uploadZip['msg']));
                    return $this->resultRedirectFactory->create()->setPath('*/upload/index');
                }
            }
            $isDownloadableAllowed = $helper->isProductTypeAllowed('downloadable');
            if ($productType == 'downloadable' && $isDownloadableAllowed) {
                $uploadLinks = $helper->uploadLinks($result, $fileData);
                if ($uploadLinks['error']) {
                    $this->messageManager->addError(__($uploadLinks['msg']));
                    return $this->resultRedirectFactory->create()->setPath('*/upload/index');
                }
                if ($this->getRequest()->getParam('is_link_samples')) {
                    $uploadLinkSamples = $helper->uploadLinkSamples($result, $fileData);
                    if ($uploadLinkSamples['error']) {
                        $this->messageManager->addError(__($uploadLinkSamples['msg']));
                        return $this->resultRedirectFactory->create()->setPath('*/upload/index');
                    }
                }
                if ($this->getRequest()->getParam('is_samples')) {
                    $uploadSamples = $helper->uploadSamples($result, $fileData);
                    if ($uploadSamples['error']) {
                        $this->messageManager->addError(__($uploadSamples['msg']));
                        return $this->resultRedirectFactory->create()->setPath('*/upload/index');
                    }
                }
            }
            $message = __('Your file was uploaded and unpacked.');
            $this->messageManager->addSuccess($message);
            return $this->resultRedirectFactory->create()->setPath('*/run/index');
        } else {
            return $this->resultRedirectFactory->create()->setPath('admin/dashboard/index');
        }
    }
}
