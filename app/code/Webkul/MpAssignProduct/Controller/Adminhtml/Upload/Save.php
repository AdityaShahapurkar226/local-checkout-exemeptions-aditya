<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Controller\Adminhtml\Upload;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helper;

   /**
    * Initialization
    *
    * @param Context $context
    * @param PageFactory $resultPageFactory
    * @param \Magento\Customer\Model\Url $url
    * @param \Magento\Customer\Model\Session $session
    * @param \Webkul\MpAssignProduct\Helper\Data $helper
    */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpAssignProduct\Helper\Data $helper
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_url = $url;
        $this->_session = $session;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Save Csv Data
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->helper;
        $validateData = $this->validateUploadedFiles();
        $noValidate="";
        $files = $this->getRequest()->getFiles();
        if ($files['massupload_image']['name'] == "" || $files['massupload_image']['name'] == null) {
            $noValidate = 'image';
        }
       
        if (!$validateData['error']) {
            $productType = $validateData['type'];
            $fileName = $validateData['csv'];
            $fileData = $validateData['csv_data'];
            
            $result = $helper->saveProfileData(
                $productType,
                $fileName,
                $fileData,
                $validateData['extension']
            );
           
            $uploadCsv = $helper->uploadCsv($result, $validateData['extension'], $fileName);
           
            if ($uploadCsv['error']) {
                $this->messageManager->addError(__($uploadCsv['msg']));
                return $this->resultRedirectFactory->create()->setPath('*/*/view');
            }
            
            if (empty($noValidate)) {
                $uploadZip = $helper->uploadZip($result, $fileData);
                if ($uploadZip['error']) {
                    $this->messageManager->addError(__($uploadZip['msg']));
                    return $this->resultRedirectFactory->create()->setPath('*/*/view');
                }
            }
            $message = __('Your file was uploaded and unpacked.');
            $this->messageManager->addSuccess($message);
            return $this->resultRedirectFactory->create()->setPath('*/*/view');
        } else {
            return $this->resultRedirectFactory->create()->setPath('*/*/view');
        }
    }
    /**
     * Validate upload csv
     *
     * @return void
     */
    protected function validateUploadedFiles()
    {
        $noValidate="";
        $files = $this->getRequest()->getFiles();
        if ($files['massupload_image']['name'] == "" || $files['massupload_image']['name'] == null) {
            $noValidate = 'image';
        }
        $helper = $this->helper;
        $validateData = $helper->validateUploadedFiles($noValidate);
        if ($validateData['error']) {
            $this->messageManager->addError(__($validateData['msg']));
        }
        return $validateData;
    }
    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpAssignProduct::massupload');
    }
}
