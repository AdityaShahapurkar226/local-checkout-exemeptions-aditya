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

class DeleteSubSellerAccount implements ResolverInterface
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
            if (empty($params['id']) || empty($customerId)) {
                throw new GraphQlAuthorizationException(__('"id" and "customerId" fields are required'));
            }
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
            $message['error'] = 0;
            $message['message'] = '';
            $ids = [];
            $count = 0;
            $comma = ',';
            $value = $this->_subAccount;
            if (strpos($params['id'], $comma) !== false) {
                $ids = explode(',', $args['id']);
            } else {
                $ids[] = $args['id'];
            }
            foreach ($ids as $id) {
                if (empty($id)) {
                    throw new GraphQlNoSuchEntityException(__('Please enter valid ids'));
                }
                $SubSellerCollection = $this->subAccountCollection->create()
                                        ->addFieldtoFilter('seller_id', $customerId)
                                        ->addFieldtoFilter('entity_id', $id);
                $value->load($id);
                if ($value && $value->getSellerId() == $customerId && $SubSellerCollection->getSize() > 0) {
                    $value->delete();
                    $this->_mpSubSellerHelper->saveCustomerGroupData(
                        $customerId
                    );
                    $count++;
                } else {
                    $invalidIdsArr[] = $id;
                    $message['error'] = 1;
                }
            }
            if ($message['error']) {
                $invalidIds = implode(',', $invalidIdsArr);
                $message['message'] = __('%1 account deleted %2 id not found', $count, $invalidIds);
                throw new GraphQlNoSuchEntityException($message['message']);
            }
            return[
                'message' => __('%1 accounts deleted successfully', $count)
            ];
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
        }
    }
}
