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

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Webkul\Marketplace\Api\Data\FeedbackSearchResultInterfaceFactory as SearchResultFactory;

/**
 * Marketplace FeedbackRepository Class
 */
class FeedbackRepository implements \Webkul\Marketplace\Api\FeedbackRepositoryInterface
{
    /**
     * @var \Webkul\Marketplace\Model\FeedbackFactory
     */
    protected $modelFactory = null;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Feedback\CollectionFactory
     */
    protected $collectionFactory = null;

    /**
     * FeedbackRepository constructor.
     *
     * @param \Webkul\Marketplace\Model\FeedbackFactory $modelFactory
     * @param \Webkul\Marketplace\Model\ResourceModel\Feedback\CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchResultFactory $searchResultFactory
     */
    public function __construct(
        \Webkul\Marketplace\Model\FeedbackFactory $modelFactory,
        \Webkul\Marketplace\Model\ResourceModel\Feedback\CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        SearchResultFactory $searchResultFactory
    ) {
        $this->modelFactory = $modelFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultFactory = $searchResultFactory;
    }

    /**
     * Get by id
     *
     * @param int $id
     * @return \Webkul\Marketplace\Model\Feedback
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
     * @param \Webkul\Marketplace\Model\Feedback $subject
     * @return \Webkul\Marketplace\Model\Feedback
     */
    public function save(\Webkul\Marketplace\Model\Feedback $subject)
    {
        try {
            $subject->save();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__($exception->getMessage()));
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
     * @param \Webkul\Marketplace\Model\Feedback $subject
     * @return boolean
     */
    public function delete(\Webkul\Marketplace\Model\Feedback $subject)
    {
        try {
            $subject->delete();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__($exception->getMessage()));
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
