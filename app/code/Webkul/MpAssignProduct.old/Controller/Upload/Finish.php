<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Controller\Upload;

use Magento\Framework\App\Action\Context;

class Finish extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Webkul\MpMassUpload\Helper\Data $massUploadHelper
     */
    protected $_massUploadHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @param Context $context
     * @param \Webkul\MpMassUpload\Helper\Data $massUploadHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MpAssignProduct\Api\ProfileRepositoryInterface $profileFactory
    ) {
        $this->_jsonHelper = $jsonHelper;
        $this->profileFactory = $profileFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!empty($this->getRequest()->getPost())) {
            $result = [];
            $profileId = $this->getRequest()->getParam('id');
            $total = (int) $this->getRequest()->getParam('row');
            $skipCount = (int) $this->getRequest()->getParam('skip');
            $total = $total - $skipCount;
            $msg = '<div class="wk-mu-success wk-mu-box">';
            $msg .= __('Total %1 Product(s) Imported.', $total);
            $msg .= '</div>';
            $msg .= '<div class="wk-mu-note wk-mu-box">';
            $msg .= __('Finished Execution.');
            $msg .= '</div>';
            $result['msg'] = $msg;
            $this->deleteProfile($profileId);
            $result = $this->_jsonHelper->jsonEncode($result);
            $this->getResponse()->representJson($result);
        }
    }

    /**
     * delete CSV Profile
     *
     * @param [int] $profileId
     * @return void
     */
    public function deleteProfile($profileId)
    {
        try {
            $this->profileFactory->getById($profileId)->delete();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }
}
