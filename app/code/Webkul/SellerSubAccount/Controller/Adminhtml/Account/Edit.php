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

namespace Webkul\SellerSubAccount\Controller\Adminhtml\Account;

/**
 * Webkul SellerSubAccount Account Edit Controller.
 */
class Edit extends \Webkul\SellerSubAccount\Controller\Adminhtml\AbstractSubAccount
{
    /**
     * Seller Sub Account Edit action.
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $subAccountId = $this->initCurrentSubAccountId();
        $isExistingSubAccount = (bool)$subAccountId;
        if ($isExistingSubAccount) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            try {
                $id = (int)$this->getRequest()->getParam('id');
                // Check If sub account does not exists
                $subAccount = $this->_subAccountRepository->get($id);
                if ($subAccount->getId()) {
                    $this->_coreRegistry->register(
                        'seller_id',
                        $subAccount->getSellerId()
                    );
                    $resultPage = $this->_resultPageFactory->create();
                    $resultPage->getConfig()->getTitle()->set(
                        __('Edit Sub-Account')
                    );
                    return $resultPage;
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
        if ($isExistingSubAccount) {
            $resultPage->getConfig()->getTitle()->set(
                __('Edit Sub Account with id %1', $subAccountId)
            );
        } else {
            $sellerId = (int)$this->getRequest()->getParam('seller_id');
            if ($sellerId) {
                $this->_coreRegistry->register(
                    'seller_id',
                    $sellerId
                );
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/seller/index',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
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
        if ($subAccountId) {
            $this->_coreRegistry->register(
                'sub_account',
                $subAccountId
            );
        }
        return $subAccountId;
    }
}
