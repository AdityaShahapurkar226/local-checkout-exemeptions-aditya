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

use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Webkul SellerSubAccount Account Save Controller.
 */
class Save extends \Webkul\SellerSubAccount\Controller\Adminhtml\AbstractSubAccount
{
    /**
     * Seller Sub Account Account Save action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $returnToEdit = false;
        $postData = $this->getRequest()->getPostValue();
        $id = $this->getCurrentAccountId();
        $sellerId = $this->getCurrentSellerId();
        if ($this->getRequest()->isPost()) {
            try {
                $postData = $this->validateData($postData);
                if (!empty($id)) {
                    // Check If sub account does not exists
                    $subAccount = $this->_subAccountRepository->get($id);
                    if ($subAccount->getId()) {
                        $result = $this->_helperData->saveCustomerData(
                            $postData['sub_account'],
                            $subAccount->getCustomerId(),
                            $this->_marketplaceHelper->getWebsiteId()
                        );
                        if (!empty($result['error']) && $result['error'] == 1) {
                            $this->messageManager->addError(
                                $result['message']
                            );
                            return $this->resultRedirectFactory->create()->setPath(
                                'sellersubaccount/account/manage',
                                ['_secure' => $this->getRequest()->isSecure()]
                            );
                        } else {
                            $customerId = $result['customer_id'];
                        }
                        $value = $this->_subAccount->load($id);
                        $value->setPermissionType(
                            implode(',', $postData['sub_account']['permission_type'])
                        );
                        $value->setStatus($postData['sub_account']['status']);
                        $value->save();
                        $this->messageManager->addSuccess(
                            __('Sub Account was successfully saved.')
                        );
                        $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
                    } else {
                        $this->messageManager->addError(
                            __('Sub Account does not exist.')
                        );
                        $returnToEdit = false;
                    }
                } else {
                    $result = $this->_helperData->saveCustomerData($postData['sub_account']);
                    if (!empty($result['error']) && $result['error'] == 1) {
                        $this->messageManager->addError(
                            $result['message']
                        );
                        return $this->resultRedirectFactory->create()->setPath(
                            'sellersubaccount/account/manage',
                            [
                                'seller_id'=>$sellerId,
                                '_current' => true,
                                '_secure' => $this->getRequest()->isSecure()
                            ]
                        );
                    } else {
                        $customerId = $result['customer_id'];
                    }
                    $value = $this->_subAccount;
                    $value->setSellerId($sellerId);
                    $value->setCustomerId($customerId);
                    $value->setPermissionType(
                        implode(',', $postData['sub_account']['permission_type'])
                    );
                    $value->setStatus($postData['sub_account']['status']);
                    $value->setCreatedDate($this->_date->gmtDate());
                    $id = $value->save()->getId();
                    $this->messageManager->addSuccess(
                        __('Sub Account was successfully created.')
                    );
                    $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $returnToEdit = true;
            }
        }
        if ($returnToEdit) {
            if ($id) {
                return $this->resultRedirectFactory->create()->setPath(
                    'sellersubaccount/account/edit',
                    [
                        'id'=>$id,
                        '_current' => true,
                        '_secure' => $this->getRequest()->isSecure()
                    ]
                );
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'sellersubaccount/account/edit',
                    [
                        'seller_id'=>$sellerId,
                        '_current' => true,
                        '_secure' => $this->getRequest()->isSecure()
                    ]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'sellersubaccount/account/manage',
                [
                    'seller_id'=>$sellerId,
                    '_current' => true,
                    '_secure' => $this->getRequest()->isSecure()
                ]
            );
        }
    }

    /**
     * Validate Sub Account Data.
     *
     * @param array $postData
     * @return array
     */
    public function validateData($postData)
    {
        if (!empty($postData['sub_account']['seller_id'])) {
            $sellerId = $postData['sub_account']['seller_id'];
            $customer = $this->_helperData->getCustomerById($sellerId);
            if (!$customer->getId()) {
                throw new LocalizedException(
                    __('Seller account does not exist.')
                );
            }
        } else {
            throw new LocalizedException(
                __('Please select valid master seller account.')
            );
        }
        if (empty($postData['sub_account']['permission_type'])) {
            $postData['sub_account']['permission_type'] = [];
        }
        if (empty($postData['sub_account']['status'])) {
            $postData['sub_account']['status'] = 0;
        }
        return $postData;
    }

    /**
     * Retrieve current account ID
     *
     * @return int
     */
    private function getCurrentAccountId()
    {
        $originalRequestData = $this->getRequest()->getPostValue('sub_account');

        $id = isset($originalRequestData['entity_id'])
            ? $originalRequestData['entity_id']
            : null;

        return $id;
    }

    /**
     * Retrieve current seller ID
     *
     * @return int
     */
    private function getCurrentSellerId()
    {
        $originalRequestData = $this->getRequest()->getPostValue('sub_account');

        $sellerId = isset($originalRequestData['seller_id'])
            ? $originalRequestData['seller_id']
            : null;

        return $sellerId;
    }
}
