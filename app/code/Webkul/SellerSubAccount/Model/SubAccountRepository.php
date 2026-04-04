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
namespace Webkul\SellerSubAccount\Model;

use Webkul\SellerSubAccount\Api\SubAccountRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\SellerSubAccount\Api\Data\SubAccountInterface;
use Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\CollectionFactory;
use Webkul\SellerSubAccount\Model\ResourceModel\SubAccount as ResourceModelSubAccount;

/**
 * SubAccountRepository :class to implement SubAccountRepositoryInterface define its method
 */
class SubAccountRepository implements SubAccountRepositoryInterface
{
    /**
     * @var SubAccountFactory
     */
    public $_subAccountFactory;

    /**
     * @var SubAccount[]
     */
    public $_instancesById = [];

    /**
     * @var CollectionFactory
     */
    public $_collectionFactory;

    /**
     * @var ResourceModelSubAccount
     */
    public $_resourceModel;

    /**
     * @param SubAccountFactory       $subAccountFactory
     * @param CollectionFactory       $collectionFactory
     * @param ResourceModelSubAccount $resourceModel
     */
    public function __construct(
        SubAccountFactory $subAccountFactory,
        CollectionFactory $collectionFactory,
        ResourceModelSubAccount $resourceModel
    ) {
        $this->_subAccountFactory = $subAccountFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_resourceModel = $resourceModel;
    }

    /**
     * @inheritdoc
     */
    public function get($entityId)
    {
        $subAccountData = $this->_subAccountFactory->create();
        $subAccountData->load($entityId);
        if (!$subAccountData->getId()) {
            $this->_instancesById[$entityId] = $subAccountData;
        }
        $this->_instancesById[$entityId] = $subAccountData;

        return $this->_instancesById[$entityId];
    }

    /**
     * @inheritdoc
     */
    public function getBySellerId($sellerId)
    {
        $subAccountCollection = $this->_collectionFactory->create()
                ->addFieldToFilter('seller_id', $sellerId);
        $subAccountCollection->load();

        return $subAccountCollection;
    }

    /**
     * @inheritdoc
     */
    public function getActiveByCustomerId($customerId)
    {
        $subAccountCollection = $this->_collectionFactory->create()
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('status', 1);
        $subAccountCollection->load();
        $id = 0;
        foreach ($subAccountCollection as $subAccount) {
            $id = $subAccount->getId();
        }

        return $this->get($id);
    }

    /**
     * @inheritdoc
     */
    public function getByCustomerId($customerId)
    {
        $subAccountCollection = $this->_collectionFactory->create()
                ->addFieldToFilter('customer_id', $customerId);
        $subAccountCollection->load();
        $id = 0;
        foreach ($subAccountCollection as $subAccount) {
            $id = $subAccount->getId();
        }

        return $this->get($id);
    }

    /**
     * @inheritdoc
     */
    public function getActiveAccountsBySellerId($sellerId)
    {
        $subAccountCollection = $this->_collectionFactory->create()
                ->addFieldToFilter('seller_id', $sellerId)
                ->addFieldToFilter('status', 1);
        $subAccountCollection->load();

        return $subAccountCollection;
    }

    /**
     * @inheritdoc
     */
    public function getList()
    {
        /** @var \Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $collection->load();

        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function delete(SubAccountInterface $subAccount)
    {
        $entityId = $subAccount->getId();
        try {
            $this->_resourceModel->delete($subAccount);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove seller sub account with id %1', $entityId)
            );
        }
        unset($this->_instancesById[$entityId]);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($entityId)
    {
        $subAccount = $this->get($entityId);

        return $this->delete($subAccount);
    }

    /**
     * @inheritdoc
     */
    public function deleteBySellerId($sellerId)
    {
        $subAccount = $this->getBySellerId($sellerId);

        return $this->delete($subAccount);
    }
}
