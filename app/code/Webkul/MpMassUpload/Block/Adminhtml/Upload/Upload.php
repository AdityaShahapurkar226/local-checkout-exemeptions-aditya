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
namespace Webkul\MpMassUpload\Block\Adminhtml\Upload;

class Upload extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Webkul\MpMassUpload\Helper\Data
     */
    protected $_massUploadHelper;
    
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Webkul\MpMassUpload\Helper\Data $massUploadHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Webkul\MpMassUpload\Helper\Data $massUploadHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->_massUploadHelper = $massUploadHelper;
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }
    
    /**
     * Construct params
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Webkul_MpMassUpload';
        $this->_controller = 'adminhtml_upload';
        parent::_construct();
        $this->buttonList->remove('delete');
        $this->buttonList->update('save', 'label', __('Upload'));
        $this->_formInitScripts[] = '
        require([
            "jquery"
        ], function ($) {
            $("#save").click(function () {
                if ($("#edit_form").valid()) {
                    $("body").trigger("processStart");
                }
                return false;
            });
        });';
    }

    /**
     * Get Header Text.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Manage Mass Upload');
    }

    /**
     * Check permission for passed action.
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Get Sample Csv File Urls.
     *
     * @return array
     */
    public function getSampleCsv()
    {
        return $this->_massUploadHelper->getSampleCsv();
    }

    /**
     * Get Sample XML File Urls.
     *
     * @return array
     */
    public function getSampleXml()
    {
        return $this->_massUploadHelper->getSampleXml();
    }

    /**
     * Get Sample XLS File Urls.
     *
     * @return array
     */
    public function getSampleXls()
    {
        return $this->_massUploadHelper->getSampleXls();
    }

    /**
     * Check Whether Product Type is Allowed or Not
     *
     * @param string $type
     * @return bool
     */
    public function isProductTypeAllowed($type)
    {
        return $this->_massUploadHelper->isProductTypeAllowed($type);
    }

    /**
     * Get attribute profiles
     *
     * @param integer $flag
     * @return array
     */
    public function getAttributeProfiles($flag = 0)
    {
        return $this->_massUploadHelper->getAttributeProfiles($flag);
    }

    /**
     * Encodes data in json format
     *
     * @param array $data
     * @return string
     */
    public function jsonEncode($data)
    {
        return $this->_jsonHelper->jsonEncode($data);
    }
}
