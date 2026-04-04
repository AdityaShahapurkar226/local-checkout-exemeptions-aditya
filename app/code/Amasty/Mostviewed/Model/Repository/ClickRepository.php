<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Repository;

use Amasty\Mostviewed\Api\Data\ClickInterface;
use Amasty\Mostviewed\Api\ClickRepositoryInterface;
use Amasty\Mostviewed\Model\Analytics\ClickFactory;
use Amasty\Mostviewed\Model\ResourceModel\Analytics\Click as ClickResource;
use Amasty\Mostviewed\Model\ResourceModel\Analytics\Click\CollectionFactory;
use Amasty\Mostviewed\Model\ResourceModel\Analytics\Click\Collection;
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
class ClickRepository implements ClickRepositoryInterface
{
    /**
     * @var ClickFactory
     */
    private $clickFactory;

    /**
     * @var ClickResource
     */
    private $clickResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $clicks = [];

    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private $clickCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        ClickFactory $clickFactory,
        ClickResource $clickResource,
        CollectionFactory $clickCollectionFactory
    ) {
        $this->clickFactory = $clickFactory;
        $this->clickResource = $clickResource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->clickCollectionFactory = $clickCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function save(ClickInterface $click)
    {
        try {
            if ($click->getId()) {
                $click = $this->getById($click->getId())->addData($click->getData());
            }
            $this->clickResource->save($click);
            unset($this->clicks[$click->getId()]);
        } catch (\Exception $e) {
            if ($click->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save click with ID %1. Error: %2',
                        [$click->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new click. Error: %1', $e->getMessage()));
        }

        return $click;
    }

    /**
     * @inheritdoc
     */
    public function getById($id)
    {
        if (!isset($this->clicks[$id])) {
            /** @var \Amasty\Mostviewed\Model\Click $click */
            $click = $this->clickFactory->create();
            $this->clickResource->load($click, $id);
            if (!$click->getId()) {
                throw new NoSuchEntityException(__('Click with specified ID "%1" not found.', $id));
            }
            $this->clicks[$id] = $click;
        }

        return $this->clicks[$id];
    }

    /**
     * @inheritdoc
     */
    public function delete(ClickInterface $click)
    {
        try {
            $this->clickResource->delete($click);
            unset($this->clicks[$click->getId()]);
        } catch (\Exception $e) {
            if ($click->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove click with ID %1. Error: %2',
                        [$click->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove click. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($id)
    {
        $clickModel = $this->getById($id);
        $this->delete($clickModel);

        return true;
    }

    /**
     * @return int
     */
    public function getCountLoaded()
    {
        return count($this->clicks);
    }

    /**
     * @inheritdoc
     */
    public function deleteLoaded()
    {
        foreach ($this->clicks as $click) {
            $this->delete($click);
        }
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Mostviewed\Model\ResourceModel\Analytics\Click\Collection $clickCollection */
        $clickCollection = $this->clickCollectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $clickCollection);
        }
        $searchResults->setTotalCount($clickCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $clickCollection);
        }
        $clickCollection->setCurPage($searchCriteria->getCurrentPage());
        $clickCollection->setPageSize($searchCriteria->getPageSize());
        $clicks = [];
        /** @var ClickInterface $click */
        foreach ($clickCollection->getItems() as $click) {
            $clicks[] = $this->getById($click->getId());
        }
        $searchResults->setItems($clicks);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $clickCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $clickCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $clickCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $clickCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $clickCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $clickCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }
}
