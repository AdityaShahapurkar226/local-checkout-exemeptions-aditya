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

use Magento\Framework\Api\SearchResultsInterfaceFactory as SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * MpApi SaleslistRepository Class
 */
class SaleslistRepository implements \Webkul\MpApi\Api\SaleslistRepositoryInterface
{
    /**
     * @var \Webkul\Marketplace\Api\Data\SaleslistInterfaceFactory
     */
    protected $modelFactory = null;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory
     */
    protected $collectionFactory = null;

    /**
     * SaleslistRepository constructor.
     *
     * @param \Webkul\Marketplace\Api\Data\SaleslistInterfaceFactory $modelFactory
     * @param \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory $collectionFactory
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \Webkul\Marketplace\Api\Data\SaleslistInterfaceFactory $modelFactory,
        \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory $collectionFactory,
        SearchResultFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->modelFactory = $modelFactory;
        $this->searchResultFactory=$searchResultFactory;
        $this->collectionProcessor=$collectionProcessor;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get saleslist by id
     *
     * @param int $id
     * @return \Webkul\Marketplace\Api\Data\SaleslistInterface
     */
    public function getById($id)
    {
        $model = $this->modelFactory->create()->load($id);
        if (!$model->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The record with the "%1" ID doesn\'t exist.', $id)
            );
        }
        return $model;
    }

    /**
     * Save record
     *
     * @param \Webkul\Marketplace\Api\Data\SaleslistInterface $subject
     * @return \Webkul\Marketplace\Api\Data\SaleslistInterface
     */
    public function save(\Webkul\Marketplace\Api\Data\SaleslistInterface $subject)
    {
        try {
            $subject->save();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __($exception->getMessage())
            );
        }
        return $subject;
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
     * Delete record
     *
     * @param \Webkul\Marketplace\Api\Data\SaleslistInterface $subject
     * @return boolean
     */
    public function delete(\Webkul\Marketplace\Api\Data\SaleslistInterface $subject)
    {
        try {
            $subject->delete();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(
                __($exception->getMessage())
            );
        }
        return true;
    }

    /**
     * Delete record by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
