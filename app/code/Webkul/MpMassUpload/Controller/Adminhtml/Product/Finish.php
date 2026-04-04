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
namespace Webkul\MpMassUpload\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;

class Finish extends \Magento\Backend\App\Action
{
    /**
     * @var \Webkul\MpMassUpload\Model\ProfileFactory
     */
    protected $_profile;

    /**
     * @var \Webkul\MpMassUpload\Helper\Data
     */
    protected $_massUploadHelper;

    /**
     * @param Context $context
     * @param \Webkul\MpMassUpload\Helper\Data $massUploadHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        Context $context,
        \Webkul\MpMassUpload\Helper\Data $massUploadHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->_massUploadHelper = $massUploadHelper;
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * Return Execute
     *
     * @return string
     */
    public function execute()
    {
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
        $this->_massUploadHelper->deleteProfile($profileId);
        $result = $this->_jsonHelper->jsonEncode($result);
        $this->getResponse()->representJson($result);
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpMassUpload::run');
    }
}
