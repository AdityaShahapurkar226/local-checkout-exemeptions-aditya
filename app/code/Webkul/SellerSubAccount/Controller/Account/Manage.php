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
namespace Webkul\SellerSubAccount\Controller\Account;

class Manage extends \Webkul\SellerSubAccount\Controller\AbstractSubAccount
{
    /**
     * Execute
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->_helper->manageSubAccounts()) {
            return $this->resultRedirectFactory->create()->setPath(
                'customer/account/',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        if (!$this->_marketplaceHelper->isSeller()) {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        $resultPage = $this->_resultPageFactory->create();
        if ($this->_marketplaceHelper->getIsSeparatePanel()) {
            $resultPage->addHandle('sellersubaccount_layout2_account_manage');
        }
        $resultPage->getConfig()->getTitle()->set(__('Seller Sub Accounts'));
        return $resultPage;
    }
}
