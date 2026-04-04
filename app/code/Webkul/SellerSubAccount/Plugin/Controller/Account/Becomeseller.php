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
namespace Webkul\SellerSubAccount\Plugin\Controller\Account;

use Webkul\SellerSubAccount\Helper\Data as HelperData;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Controller\Result\RedirectFactory;

class Becomeseller
{
    /**
     * @var HelperData
     */
    public $_helper;

    /**
     * @var ManagerInterface
     */
    private $_messageManager;

    /**
     * @var RedirectFactory
     */
    public $_resultRedirectFactory;

    /**
     * @param HelperData       $helper
     * @param ManagerInterface $messageManager
     * @param RedirectFactory  $resultRedirectFactory
     */
    public function __construct(
        HelperData $helper,
        ManagerInterface $messageManager,
        RedirectFactory $resultRedirectFactory
    ) {
        $this->_helper = $helper;
        $this->_messageManager = $messageManager;
        $this->_resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * Function to run to change the retun data of excute method.
     *
     * @param \Webkul\Marketplace\Controller\Account\Becomeseller $controller
     * @param \Closure $proceed
     *
     * @return bool
     */
    public function aroundExecute(
        \Webkul\Marketplace\Controller\Account\Becomeseller $controller,
        \Closure $proceed
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            return $proceed();
        }
        $this->_messageManager->addError(__('You are not allowed to perform this action.'));
        if (in_array("marketplace/product/productlist", $this->subAccountPermissionsAllowedByAdmin())) {
            return $this->_resultRedirectFactory->create()->setPath(
                'marketplace/product/productlist',
                ['_secure' => $controller->getRequest()->isSecure()]
            );
        } else {
            return $this->_resultRedirectFactory->create()->setPath(
                'customer/account',
                ['_secure' => $controller->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Sub Account Permissions Allowed By Admin
     *
     * @return array
     */
    public function subAccountPermissionsAllowedByAdmin()
    {
        $subAccountPermissions = $this->_helper->getSellerPermissionByCustomerId();
        return $subAccountPermissions;
    }
}
