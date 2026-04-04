<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Repository;

use Amasty\Mostviewed\Api\Data\AnalyticInterface;
use Amasty\Mostviewed\Api\AnalyticRepositoryInterface;
use Amasty\Mostviewed\Model\Analytics\AnalyticFactory;
use Amasty\Mostviewed\Model\ResourceModel\Analytics\Analytic as AnalyticResource;
use Amasty\Mostviewed\Model\ResourceModel\Analytics\Analytic\CollectionFactory;
use Amasty\Mostviewed\Model\ResourceModel\Analytics\Analytic\Collection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AnalyticRepository implements AnalyticRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var AnalyticFactory
     */
    private $analyticFactory;

    /**
     * @var AnalyticResource
     */
    private $analyticResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $analytics;

    /**
     * @var CollectionFactory
     */
    private $analyticCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        AnalyticFactory $analyticFactory,
        AnalyticResource $analyticResource,
        CollectionFactory $analyticCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->analyticFactory = $analyticFactory;
        $this->analyticResource = $analyticResource;
        $this->analyticCollectionFactory = $analyticCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(AnalyticInterface $analytic)
    {
        try {
            if ($analytic->getId()) {
                $analytic = $this->getById($analytic->getId())->addData($analytic->getData());
            }
            $this->analyticResource->save($analytic);
            unset($this->analytics[$analytic->getId()]);
        } catch (\Exception $e) {
            if ($analytic->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save analytic with ID %1. Error: %2',
                        [$analytic->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new analytic. Error: %1', $e->getMessage()));
        }

        return $analytic;
    }

    /**
     * @inheritdoc
     */
    public function getNew()
    {
        return $this->analyticFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        if (!isset($this->analytics[$id])) {
            /** @var \Amasty\Mostviewed\Model\Analytic $analytic */
            $analytic = $this->analyticFactory->create();
            $this->analyticResource->load($analytic, $id);
            if (!$analytic->getId()) {
                throw new NoSuchEntityException(__('Analytic with specified ID "%1" not found.', $id));
            }
            $this->analytics[$id] = $analytic;
        }

        return $this->analytics[$id];
    }

    /**
     * @inheritdoc
     */
    public function delete(AnalyticInterface $analytic)
    {
        try {
            $this->analyticResource->delete($analytic);
            unset($this->analytics[$analytic->getId()]);
        } catch (\Exception $e) {
            if ($analytic->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove analytic with ID %1. Error: %2',
                        [$analytic->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove analytic. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $analyticModel = $this->getById($id);
        $this->delete($analyticModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Mostviewed\Model\ResourceModel\Analytics\Analytic\Collection $analyticCollection */
        $analyticCollection = $this->analyticCollectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $analyticCollection);
        }
        $searchResults->setTotalCount($analyticCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $analyticCollection);
        }
        $analyticCollection->setCurPage($searchCriteria->getCurrentPage());
        $analyticCollection->setPageSize($searchCriteria->getPageSize());
        $analytics = [];
        /** @var AnalyticInterface $analytic */
        foreach ($analyticCollection->getItems() as $analytic) {
            $analytics[] = $this->getById($analytic->getId());
        }
        $searchResults->setItems($analytics);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $analyticCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $analyticCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $analyticCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $analyticCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $analyticCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $analyticCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }
}
