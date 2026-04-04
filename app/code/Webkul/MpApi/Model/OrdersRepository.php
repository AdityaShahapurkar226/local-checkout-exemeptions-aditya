<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpApi
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpApi\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Webkul\Marketplace\Model\OrdersFactory as OrdersFactory;
use Webkul\Marketplace\Model\ResourceModel\Orders\CollectionFactory;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use \Webkul\MpApi\Api\OrdersRepositoryInterface;

class OrdersRepository extends \Webkul\Marketplace\Model\OrdersRepository implements OrdersRepositoryInterface
{
    /**
     * @param OrdersFactory $ordersFactory
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultFactory
     * @param \Webkul\Marketplace\Api\Data\SaleslistInterfaceFactory $saleslistFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        OrdersFactory $ordersFactory,
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultFactory,
        \Webkul\Marketplace\Api\Data\SaleslistInterfaceFactory $saleslistFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->saleslistFactory = $saleslistFactory;
        $this->searchResultFactory=$searchResultFactory;
        $this->collectionProcessor=$collectionProcessor;
        parent::__construct($ordersFactory, $collectionFactory);
    }

    /**
     * @inheritDoc
     */
    public function save(\Webkul\Marketplace\Api\Data\OrdersInterface $order)
    {
        try {
            $order->save();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __($exception->getMessage())
            );
        }
        return $order;
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        $model = $this->ordersFactory->create()->load($id);
        if (!$model->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The order with the "%1" ID doesn\'t exist.', $id)
            );
        }
        return $model;
    }

    /**
     * Get list
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $creteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getLists(\Magento\Framework\Api\SearchCriteriaInterface $creteria)
    {
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($creteria, $collection);

        $searchResult = $this->searchResultFactory->create();
        $searchResult->setSearchCriteria($creteria);
        $searchResult->setItems($collection->getData());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * Delete order
     *
     * @param \Webkul\Marketplace\Api\Data\OrdersInterface $order
     * @return boolean
     */
    public function delete(\Webkul\Marketplace\Api\Data\OrdersInterface $order)
    {
        try {
            $order->delete();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(
                __($exception->getMessage())
            );
        }
        return true;
    }

    /**
     * Delete order by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id)
    {
        $order = $this->get($id);

        return $this->delete($order);
    }
}
