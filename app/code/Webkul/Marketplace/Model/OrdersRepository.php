<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\Marketplace\Api\Data\OrdersInterface;
use Webkul\Marketplace\Model\ResourceModel\Orders\CollectionFactory;

class OrdersRepository implements \Webkul\Marketplace\Api\OrdersRepositoryInterface
{
    /**
     * @var OrdersFactory
     */
    protected $ordersFactory;

    /**
     * @var Orders[]
     */
    protected $instancesById = [];

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param OrdersFactory       $ordersFactory
     * @param CollectionFactory   $collectionFactory
     */
    public function __construct(
        OrdersFactory $ordersFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->ordersFactory = $ordersFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get by id
     *
     * @param int $id
     * @return \Webkul\Marketplace\Model\Orders
     */
    public function getById($id)
    {
        $ordersData = $this->ordersFactory->create();
        $ordersData->load($id);
        if (!$ordersData->getId()) {
            $this->instancesById[$id] = $ordersData;
        }
        $this->instancesById[$id] = $ordersData;

        return $this->instancesById[$id];
    }

    /**
     * Get by sellerid
     *
     * @param int||null $sellerId
     * @return \Webkul\Marketplace\Model\ResourceModel\Orders\Collection
     */
    public function getBySellerId($sellerId = null)
    {
        $ordersCollection = $this->collectionFactory->create()
        ->addFieldToFilter('seller_id', $sellerId);
        $ordersCollection->load();

        return $ordersCollection;
    }

    /**
     * Get by orderid
     *
     * @param int $orderId
     * @return \Webkul\Marketplace\Model\ResourceModel\Orders\Collection
     */
    public function getByOrderId($orderId)
    {
        $ordersCollection = $this->collectionFactory->create()
        ->addFieldToFilter('order_id', $orderId);
        $ordersCollection->load();

        return $ordersCollection;
    }

    /**
     * Get Collection
     *
     * @return \Webkul\Marketplace\Model\ResourceModel\Orders\Collection
     */
    public function getList()
    {
        /** @var \Webkul\Marketplace\Model\ResourceModel\Orders\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->load();

        return $collection;
    }
}
