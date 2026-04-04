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

/**
 * Webkul SellerSubAccount Account Edit Controller.
 */
class Edit extends \Webkul\SellerSubAccount\Controller\AbstractSubAccount
{
    /**
     * Seller Sub Account Edit action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
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
        $subAccountId = $this->initCurrentSubAccountId();
        $isExistingSubAccount = (bool)$subAccountId;
        if ($isExistingSubAccount) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            try {
                $sellerId = $this->_helper->getCustomerId();
                $id = (int)$this->getRequest()->getParam('id');
                // Check If sub account does not exists
                $subAccount = $this->_subAccountRepository->get($id);
                if ($subAccount->getId()) {
                    if ($subAccount->getSellerId() == $sellerId) {
                        $resultPage = $this->_resultPageFactory->create();
                        if ($this->_marketplaceHelper->getIsSeparatePanel()) {
                            $resultPage->addHandle('sellersubaccount_layout2_account_edit');
                        }
                        $resultPage->getConfig()->getTitle()->set(
                            __('Edit Sub-Account')
                        );
                        return $resultPage;
                    } else {
                        $this->messageManager->addError(
                            __('You are not authorized to update this sub account.')
                        );
                    }
                } else {
                    $this->messageManager->addError(
                        __('Requested sub account doesn\'t exist.')
                    );
                }
                return $this->resultRedirectFactory->create()->setPath(
                    'sellersubaccount/account/manage',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $this->resultRedirectFactory->create()->setPath(
                    'sellersubaccount/account/manage',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        }
        $resultPage = $this->_resultPageFactory->create();
        if ($this->_marketplaceHelper->getIsSeparatePanel()) {
            $resultPage->addHandle('sellersubaccount_layout2_account_edit');
        }
        if ($isExistingSubAccount) {
            $resultPage->getConfig()->getTitle()->set(
                __('Edit Sub Account with id %1', $subAccountId)
            );
        } else {
            $resultPage->getConfig()->getTitle()->set(
                __('New Sub Account')
            );
        }
        return $resultPage;
    }

    /**
     *  Sub Account Id initialization
     *
     * @return string sub Account id
     */
    public function initCurrentSubAccountId()
    {
        $subAccountId = (int)$this->getRequest()->getParam('id');

        return $subAccountId;
    }
}
