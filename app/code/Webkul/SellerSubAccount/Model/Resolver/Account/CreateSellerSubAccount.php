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

class CreateSellerSubAccount implements ResolverInterface
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
     * @inheritdoc
     */
    public function __construct(
        \Webkul\SellerSubAccount\Helper\Data $mpSubSellerHelper,
        \Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\CollectionFactory $subAccountCollection,
        CustomerCollection $customerCollection,
        Save $saveController,
        \Webkul\SellerSubAccount\Model\SubAccount $subAccount,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_mpSubSellerHelper = $mpSubSellerHelper;
        $this->customerCollection = $customerCollection;
        $this->subAccountCollection = $subAccountCollection;
        $this->_saveController = $saveController;
        $this->_subAccount = $subAccount;
        $this->_date = $date;
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
            }
            if ($this->_saveController->validateEmail($params)) {
                $result = $this->_mpSubSellerHelper->saveCustomerData($params);
                if (!empty($result['error']) && $result['error'] == 1) {
                    $message['errorMessage'] = $result['message'];
                } else {
                    $customer_Id = $result['customer_id'];
                    $value = $this->_subAccount;
                    $value->setSellerId($customerId);
                    $value->setCustomerId($customer_Id);
                    $value->setPermissionType(implode(',', $params['permission_type']));
                    $value->setStatus($params['status']);
                    $value->setCreatedDate($this->_date->gmtDate());
                    $id = $value->save()->getId();
                    $message['successMessage'] = __('Sub Account was saved successfully');
                }
                return $message;
            } else {
                throw new GraphQlNoSuchEntityException(
                    __('Customer with this email registered already')
                );
            }
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        }
    }
}
