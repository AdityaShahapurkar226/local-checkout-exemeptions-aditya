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

declare(strict_types=1);

namespace Webkul\SellerSubAccount\Model\Resolver\Account;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;
use Webkul\SellerSubAccount\Controller\Account\Save;
use Webkul\SellerSubAccount\Api\SubAccountRepositoryInterface;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;

class UpdateSellerSubAccount implements ResolverInterface
{
    /**
     * @var Magento\Catalog\Model\ProductRepository
     */
    protected $_mpSubSellerHelper;

    /**
     * @var Webkul\SellerSubAccount\Controller\Account\Save
     */
    protected $_saveController;

    /**
     * @var \Webkul\SellerSubAccount\Model\SubAccount
     */
    protected $_subAccount;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var Webkul\SellerSubAccount\Api\SubAccountRepositoryInterface
     */
    protected $_subAccountRepository;

    /**
     * @var MarketplaceHelper
     */
    protected $_marketplaceHelper;

    /**
     * @inheritdoc
     */
    public function __construct(
        \Webkul\SellerSubAccount\Helper\Data $mpSubSellerHelper,
        \Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\CollectionFactory $subAccountCollection,
        CustomerCollection $customerCollection,
        Save $saveController,
        \Webkul\SellerSubAccount\Model\SubAccount $subAccount,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        SubAccountRepositoryInterface $subAccountRepository,
        MarketplaceHelper $marketplaceHelper
    ) {
        $this->_mpSubSellerHelper = $mpSubSellerHelper;
        $this->customerCollection = $customerCollection;
        $this->subAccountCollection = $subAccountCollection;
        $this->_saveController = $saveController;
        $this->_subAccount = $subAccount;
        $this->_date = $date;
        $this->_subAccountRepository = $subAccountRepository;
        $this->_marketplaceHelper = $marketplaceHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            /** @var ContextInterface $context */
            if (false === $context->getExtensionAttributes()->getIsCustomer()) {
                throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
            }
            $params = $args;
            $customerId = $params['customerId'];
            $id = $params['id'];
            if (empty($id) && empty($customerId)) {
                throw new GraphQlAuthorizationException(__('"customerId" and "id" fields are required'));
            }
            /**
             * Checking seller
             */
            $isSeller = $this->_mpSubSellerHelper->isSeller($customerId);
            if (!$isSeller) {
                throw new GraphQlAuthorizationException(__('Become a seller First'));
            }
            /**
             * Checking permission to manage sub-accounts
             */
            $isAllowed = $this->_mpSubSellerHelper->manageSubAccounts();
            if (!$isAllowed) {
                throw new GraphQlNoSuchEntityException(__('Seller is not allowed to manage the sub-account'));
            }
            $notAllowedPermissionTypeArr = [];
            if ($params['permissionType'] != '') {
                $permissionsSelected = explode(',', $params['permissionType']);
                $allowedPermissionsArr = $this->_mpSubSellerHelper->getSellerPermissionForSubSellerByAdmin();
                $allowedPermissions = implode(',', $allowedPermissionsArr);
                foreach ($permissionsSelected as $permissions => $permission) {
                    if (!in_array($permission, $allowedPermissionsArr)) {
                        if (empty($permission)) {
                            throw new GraphQlNoSuchEntityException(__('Please enter valid permissions type'));
                        }
                        $notAllowedPermissionTypeArr[] = $permission;
                    }
                }
                $notAllowedPermissionType = implode(',', $notAllowedPermissionTypeArr);
                if (!empty($notAllowedPermissionTypeArr)) {
                    throw new GraphQlNoSuchEntityException(
                        __(
                            "%1 these permissions are not allowed for the seller, please select from %2",
                            $notAllowedPermissionType,
                            $allowedPermissions
                        )
                    );
                }
                $params['permission_type'] = $permissionsSelected;
                $subAccount = $this->_subAccountRepository->get($id);
                if ($subAccount->getId()) {
                    if ($subAccount->getSellerId() == $customerId) {
                        $result = $this->checkAndSaveCustomerData($id, $params, $subAccount);
                        return $result;
                    } else {
                        throw new GraphQlNoSuchEntityException(
                            __('You are not authorized to update this sub account.')
                        );
                    }
                } else {
                    throw new GraphQlNoSuchEntityException(
                        __('Sub Account does not exist.')
                    );
                }
            }
            
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        }
    }

    /**
     * Check and save customer data
     *
     * @param int $id
     * @param array $postData
     * @param collection $subAccount
     * @return void
     */
    private function checkAndSaveCustomerData($id = null, $postData = null, $subAccount = null)
    {
            $result = $this->_mpSubSellerHelper->saveCustomerData(
                $postData,
                $subAccount->getCustomerId(),
                $this->_marketplaceHelper->getWebsiteId()
            );
        if (!empty($result['error']) && $result['error'] == 1) {
                return [
                    'message' => $result['message']
                ];
        } else {
                $customerId = $result['customer_id'];
        }
            $value = $this->_subAccount->load($id);
            $value->setPermissionType(
                implode(',', $postData['permission_type'])
            );
        
            $value->setStatus($postData['status']);
            $value->save();
            return [
                'message' => 'SubAccount updated successfully'
            ];
    }
}
