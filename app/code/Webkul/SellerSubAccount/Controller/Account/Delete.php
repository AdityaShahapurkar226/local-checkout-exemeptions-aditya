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
 * Webkul SellerSubAccount Account Delete Controller.
 */
class Delete extends \Webkul\SellerSubAccount\Controller\AbstractSubAccount
{
    /**
     * Seller Sub Account Delete action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        try {
            $sellerId = $this->_helper->getCustomerId();
            $id = (int)$this->getRequest()->getParam('id');
            // Check If sub account does not exists
            $subAccount = $this->_subAccountRepository->get($id);
            if ($subAccount->getId()) {
                if ($subAccount->getSellerId() == $sellerId) {
                    $customerId = $subAccount->getCustomerId();
                    $this->_subAccountRepository->deleteById($id);
                    $this->_helper->saveCustomerGroupData(
                        $customerId,
                        $this->_marketplaceHelper->getWebsiteId()
                    );
                    $this->messageManager->addSuccess(
                        __('Sub Account was successfully deleted.')
                    );
                } else {
                    $this->messageManager->addError(
                        __('You are not authorized to delete this sub account.')
                    );
                }
            } else {
                $this->messageManager->addError(
                    __('Requested sub account doesn\'t exist')
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
}
