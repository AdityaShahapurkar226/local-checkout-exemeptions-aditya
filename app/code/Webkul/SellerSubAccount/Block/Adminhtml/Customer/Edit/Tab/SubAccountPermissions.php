<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\SellerSubAccount\Block\Adminhtml\Customer\Edit\Tab;

use Magento\Customer\Controller\RegistryConstants;

class SubAccountPermissions extends \Magento\Backend\Block\Template
{
    /**
     * Block template.
     *
     * @var string
     */
    public $_template = 'Webkul_SellerSubAccount::seller/sub_account_permission.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Webkul\SellerSubAccount\Helper\Data $subAccountHelper
     * @param \Webkul\Marketplace\Block\Adminhtml\Customer\Edit $customerEditBlock
     * @param \Webkul\Marketplace\Model\SellerFactory $sellerModel
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Webkul\SellerSubAccount\Helper\Data $subAccountHelper,
        \Webkul\Marketplace\Block\Adminhtml\Customer\Edit $customerEditBlock,
        \Webkul\Marketplace\Model\SellerFactory $sellerModel,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->subAccountHelper = $subAccountHelper;
        $this->customerEditBlock = $customerEditBlock;
        $this->_sellerModel = $sellerModel;
        parent::__construct($context, $data);
    }

    /**
     * GetSellerModel function
     *
     * @return array
     */
    public function getSellerModel()
    {
        return $this->_sellerModel
        ->create()
        ->getCollection()
        ->addFieldToFilter('seller_id', $this->getCustomerId())
        ->addFieldToFilter('store_id', 0)
        ->getFirstItem()
        ->getSubAccountPermission();
    }

    /**
     * Get Seller Sub Account Permission List
     *
     * @return array
     */
    public function getSellerSubAccountPermissionList()
    {
        if ($this->getSellerModel()) {
            return explode(',', $this->getSellerModel());
        }
        return [];
    }

    /**
     * GetsellerAdminPermission function get list of seller to sub seller permission by admin
     *
     * @return array
     */
    public function getsellerAdminPermission()
    {
        return $this->subAccountHelper->getSellerPermissionForSubSellerByAdmin();
    }

    /**
     * Get All Permissions Object function
     *
     * @return array
     */
    public function getAllPermissionsObject()
    {
        return  $this->subAccountHelper->getAllPermissionTypes();
    }

    /**
     * Get Customer Id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Can Show Tab
     *
     * @return boolean
     */
    public function canShowTab()
    {
        $coll = $this->customerEditBlock->getMarketplaceUserCollection();
        $isSeller = false;
        foreach ($coll as $row) {
            $isSeller = $row->getIsSeller();
        }
        if ($this->getCustomerId() && $isSeller) {
            return true;
        }
        return false;
    }

    /**
     * Get Tab Label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Sub Account Permissions');
    }

    /**
     * Get Tab Title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Sub Account Permissions');
    }

    /**
     * Get Tab Class
     *
     * @return null
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Get Tab Url
     *
     * @return null
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Is Ajax Loaded
     *
     * @return boolean
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Is Hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
