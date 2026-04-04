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
 * MpApi FeedbackRepository Class
 */
class FeedbackRepository implements \Webkul\MpApi\Api\FeedbackRepositoryInterface
{
    /**
     * @var \Webkul\MpApi\Api\Data\FeedbackInterfaceFactory
     */
    protected $modelFactory = null;

    /**
     * feedback Repository constructor
     *
     * @param \Webkul\MpApi\Api\Data\FeedbackInterfaceFactory $modelFactory
     * @param SearchResultFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        \Webkul\MpApi\Api\Data\FeedbackInterfaceFactory $modelFactory,
        SearchResultsInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->modelFactory = $modelFactory;
        $this->searchResultFactory=$searchResultFactory;
        $this->collectionProcessor=$collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(\Webkul\MpApi\Api\Data\FeedbackInterface $feedback)
    {
        try {
            $feedback->save();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __($exception->getMessage())
            );
        }
        return $feedback;
    }

    /**
     * @inheritDoc
     */
    public function getById($id)
    {
        $model = $this->modelFactory->create()->load($id);
        if (!$model->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The feedback with the "%1" ID doesn\'t exist.', $id)
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
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $creteria)
    {
        /** @var \Webkul\Marketplace\Model\ResourceModel\Feedback\Collection $collection */
        $collection = $this->modelFactory->create()->getCollection();

        $this->collectionProcessor->process($creteria, $collection);

        $collection->load();
        $searchResult = $this->searchResultFactory->create();
        $searchResult->setSearchCriteria($creteria);
        $searchResult->setItems($collection->getData());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }

    /**
     * Delete feedback
     *
     * @param \Webkul\MpApi\Api\Data\FeedbackInterface $feedback
     * @return boolean
     */
    public function delete(\Webkul\MpApi\Api\Data\FeedbackInterface $feedback)
    {
        try {
            $feedback->delete();
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(
                __($exception->getMessage())
            );
        }
        return true;
    }

    /**
     * Delete feedback by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id)
    {
        $feedback = $this->get($id);

        return $this->delete($feedback);
    }
}
