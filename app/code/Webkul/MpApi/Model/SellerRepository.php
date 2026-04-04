<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpApi
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MpApi\Model;

use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * MpApi SellerRepository Class
 */
class SellerRepository implements \Webkul\MpApi\Api\SellerRepositoryInterface
{
    /**
     * @var \Webkul\Marketplace\Api\Data\SellerInterfaceFactory
     */
    protected $modelFactory = null;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory
     */
    protected $collectionFactory = null;

    /**
     * Seller Repository constructor.
     * @param \Webkul\Marketplace\Api\Data\SellerInterfaceFactory $modelFactory
     * @param \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $collectionFactory
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \Webkul\Marketplace\Api\Data\SellerInterfaceFactory $modelFactory,
        \Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultFactory=$searchResultFactory;
        $this->collectionProcessor=$collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(\Webkul\Marketplace\Api\Data\SellerInterface $seller)
    {
        try {
            $seller->save();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __($exception->getMessage())
            );
        }
        return $seller;
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        $model = $this->modelFactory->create()->load($id);
        if (!$model->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The seller with the "%1" ID doesn\'t exist.', $id)
            );
        }
        return $model;
    }

    /**
     * @inheritDoc
     */
    public function getDataBySellerId($id, $storeId = 0)
    {
        $collection = $this->modelFactory->create()
                ->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('seller_id', $id)
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('is_seller', 1);

        return $collection->getData();
    }

    /**
     * Get list
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $creteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $creteria)
    {
        /** @var \Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($creteria, $collection);

        $collection->load();
        $searchResult = $this->searchResultFactory->create();
        $searchResult->setSearchCriteria($creteria);
        $searchResult->setItems($collection->getData());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * Delete seller
     *
     * @param \Webkul\Marketplace\Api\Data\SellerInterface $seller
     * @return boolean
     */
    public function delete(\Webkul\Marketplace\Api\Data\SellerInterface $seller)
    {
        try {
            $seller->delete();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(
                __($exception->getMessage())
            );
        }
        return true;
    }

    /**
     * Delete seller by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id)
    {
        $seller = $this->get($id);

        return $this->delete($seller);
    }
}
