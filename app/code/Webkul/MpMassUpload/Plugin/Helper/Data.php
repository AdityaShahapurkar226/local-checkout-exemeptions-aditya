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
namespace Webkul\MpMassUpload\Plugin\Helper;

class Data
{

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_authSession = $authSession;
        $this->request = $request;
    }

    /**
     * Function to run to change the retun data of afterIsSeller.
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param array $result
     *
     * @return bool
     */
    public function afterIsSeller(\Webkul\Marketplace\Helper\Data $helperData, $result)
    {
        if (!empty($this->_authSession->getUser())) {
            $result = 1;
        }
        return $result;
    }

    /**
     * Function to run to change the retun data of afterIsRightSeller.
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param array $result
     *
     * @return bool
     */
    public function afterIsRightSeller(\Webkul\Marketplace\Helper\Data $helperData, $result)
    {
        if (!empty($this->_authSession->getUser())) {
            $result = 1;
        }
        return $result;
    }

    /**
     * Function to run to change the return data of afterIsSeller.
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param array $result
     *
     * @return array
     */
    public function afterGetControllerMappedPermissions(
        \Webkul\Marketplace\Helper\Data $helperData,
        $result
    ) {
        $result['mpmassupload/product/finish'] = 'mpmassupload/product/view';
        $result['mpmassupload/product/options'] = 'mpmassupload/product/view';
        $result['mpmassupload/product/profile'] = 'mpmassupload/product/view';
        $result['mpmassupload/product/run'] = 'mpmassupload/product/view';
        $result['mpmassupload/product/upload'] = 'mpmassupload/product/view';
        $result['mpmassupload/dataflow_profile/delete'] = 'mpmassupload/dataflow/profile';
        $result['mpmassupload/dataflow_profile/edit'] = 'mpmassupload/dataflow/profile';
        $result['mpmassupload/dataflow_profile/massDelete'] = 'mpmassupload/dataflow/profile';
        $result['mpmassupload/dataflow_profile/save'] = 'mpmassupload/dataflow/profile';
        return $result;
    }
    /**
     * Set store id in massupload
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param array $result
     *
     * @return int
     */
    public function afterGetCurrentStoreId(\Webkul\Marketplace\Helper\Data $helperData, $result)
    {
        $storeId = $this->request->getParam('store_to_upload');
        if (!empty($storeId)) {
            $result = $storeId;
        }
        return $result;
    }
}
