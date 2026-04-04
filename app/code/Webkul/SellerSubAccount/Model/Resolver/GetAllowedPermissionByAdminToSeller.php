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

namespace Webkul\SellerSubAccount\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;

class GetAllowedPermissionByAdminToSeller implements ResolverInterface
{
    /**
     * @var Magento\Catalog\Model\ProductRepository
     */
    protected $_mpSubSellerHelper;

    /**
     * @inheritdoc
     */
    public function __construct(
        \Webkul\SellerSubAccount\Helper\Data $mpSubSellerHelper,
        CustomerCollection $customerCollection
    ) {
        $this->_mpSubSellerHelper = $mpSubSellerHelper;
        $this->customerCollection = $customerCollection;
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
            $customerId = $args['customerId'];
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
            $allowedPermissionsFromAdminToSeller['permissions'] = $this->_mpSubSellerHelper
                                            ->getSellerPermissionForSubSellerByAdmin();
            return $allowedPermissionsFromAdminToSeller;
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        }
    }
}
