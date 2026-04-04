<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_Marketplace
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Model;

use Webkul\Marketplace\Api\Data\SaleslistSearchResultInterfaceFactory as SearchResultFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * Marketplace SaleslistRepository Class
 */
class SaleslistRepository implements \Webkul\Marketplace\Api\SaleslistRepositoryInterface
{
    /**
     * @var \Webkul\Marketplace\Model\SaleslistFactory
     */
    protected $modelFactory = null;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory
     */
    protected $collectionFactory = null;

    /**
     * SaleslistRepository constructor.
     *
     * @param \Webkul\Marketplace\Model\SaleslistFactory $modelFactory
     * @param \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory $collectionFactory
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \Webkul\Marketplace\Model\SaleslistFactory $modelFactory,
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
     * @return \Webkul\Marketplace\Model\Saleslist
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
     * @param \Webkul\Marketplace\Model\Saleslist $subject
     * @return \Webkul\Marketplace\Model\Saleslist
     */
    public function save(\Webkul\Marketplace\Model\Saleslist $subject)
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

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($creteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * Delete record
     *
     * @param \Webkul\Marketplace\Model\Saleslist $subject
     * @return boolean
     */
    public function delete(\Webkul\Marketplace\Model\Saleslist $subject)
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
