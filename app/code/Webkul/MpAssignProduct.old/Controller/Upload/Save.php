<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Controller\Upload;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;

class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $helper;

    /**
     * @param Context $context
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpAssignProduct\Helper\Data $helper
    ) {
        $this->_url = $url;
        $this->_session = $session;
        $this->helper = $helper;
        parent::__construct($context);
    }

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
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
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
}
