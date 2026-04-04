<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Repository;

use Amasty\Mostviewed\Api\Data\GroupInterface;
use Amasty\Mostviewed\Api\GroupRepositoryInterface;
use Amasty\Mostviewed\Model\Customer\GroupValidator;
use Amasty\Mostviewed\Model\GroupFactory;
use Amasty\Mostviewed\Model\Layout\Updater;
use Amasty\Mostviewed\Model\ResourceModel\Analytics\Analytic\Collection as AnalyticCollection;
use Amasty\Mostviewed\Model\ResourceModel\Group as GroupResource;
use Amasty\Mostviewed\Model\ResourceModel\Group\CollectionFactory;
use Amasty\Mostviewed\Model\ResourceModel\Group\Collection;
use Amasty\Mostviewed\Model\ResourceModel\RuleIndexFactory;
use Magento\Customer\Api\Data\GroupInterface as CustomerGroupInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GroupRepository implements GroupRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var GroupResource
     */
    private $groupResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $groups;

    /**
     * @var CollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * @var RuleIndexFactory
     */
    private $ruleIndexFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var Updater
     */
    private $layoutUpdater;

    /**
     * @var AnalyticCollection
     */
    private $analyticCollection;

    /**
     * @var GroupValidator
     */
    private $groupValidator;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        GroupFactory $groupFactory,
        GroupResource $groupResource,
        CollectionFactory $groupCollectionFactory,
        RuleIndexFactory $ruleIndexFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        AnalyticCollection $analyticCollection,
        Updater $layoutUpdater,
        GroupValidator $groupValidator
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->groupFactory = $groupFactory;
        $this->groupResource = $groupResource;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->ruleIndexFactory = $ruleIndexFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->layoutUpdater = $layoutUpdater;
        $this->analyticCollection = $analyticCollection;
        $this->groupValidator = $groupValidator;
    }

    /**
     * @inheritdoc
     */
    public function getGroupByIdAndPosition($entityId, $position)
    {
        $group = false;
        $groupIds = $this->ruleIndexFactory->create()
            ->getGroupByIdAndPosition($entityId, $position);
        $groups = $this->getGroups($groupIds);

        while (true) {
            if ($groups) {
                $group = array_shift($groups);
            } else {
                break;
            }

            if ($group = $this->validateGroup($group)) {
                break;
            }
        }

        return $group;
    }

    /**
     * @param $entityId
     * @return array|\Magento\Framework\Api\ExtensibleDataInterface[]|\Magento\Ui\Api\Data\BookmarkInterface[]
     * @throws NoSuchEntityException
     */
    public function getGroupsByEntityId($entityId)
    {
        $groupIds = $this->ruleIndexFactory->create()
            ->getGroupByIdAndPosition($entityId, '');
        $groups = $this->getGroups($groupIds);

        foreach ($groups as $key => $group) {
            if (!$this->validateGroup($group)) {
                unset($groups[$key]);
            }
        }

        return $groups;
    }

    /**
     * @param $groupIds
     * @return array|\Magento\Framework\Api\ExtensibleDataInterface[]|\Magento\Ui\Api\Data\BookmarkInterface[]
     * @throws NoSuchEntityException
     */
    private function getGroups($groupIds)
    {
        if ($groupIds) {
            $this->searchCriteriaBuilder->addFilter(GroupInterface::GROUP_ID, $groupIds, 'in');
            /** @var SortOrder $sortOrder */
            $sortOrder = $this->sortOrderBuilder->setField(GroupInterface::PRIORITY)
                ->setDirection(SortOrder::SORT_ASC)
                ->create();
            $this->searchCriteriaBuilder->setSortOrders([$sortOrder]);
            $groups = $this->getList($this->searchCriteriaBuilder->create())->getItems();
        }

        return $groups ?? [];
    }

    /**
     * @param GroupInterface $group
     *
     * @return GroupInterface|false
     */
    public function validateGroup($group)
    {
        /** @var GroupInterface|false $group */
        if ($group && $this->groupValidator->validate($group)) {
            return $group;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function save(GroupInterface $group)
    {
        try {
            if ($group->getGroupId()) {
                $group = $this->getById($group->getGroupId())->addData($group->getData());
            }
            $this->groupResource->save($group);
            unset($this->groups[$group->getGroupId()]);
        } catch (\Exception $e) {
            if ($group->getGroupId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save group with ID %1. Error: %2',
                        [$group->getGroupId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new group. Error: %1', $e->getMessage()));
        }

        return $group;
    }

    /**
     * @inheritdoc
     */
    public function getById($groupId)
    {
        if (!isset($this->groups[$groupId])) {
            /** @var \Amasty\Mostviewed\Model\Group $group */
            $group = $this->groupFactory->create();
            $this->groupResource->load($group, $groupId);
            if (!$group->getGroupId()) {
                throw new NoSuchEntityException(__('Group with specified ID "%1" not found.', $groupId));
            }
            $this->groups[$groupId] = $group;
        }

        return $this->groups[$groupId];
    }

    /**
     * @inheritdoc
     */
    public function delete(GroupInterface $group)
    {
        try {
            $this->layoutUpdater->delete($group->getLayoutUpdateId());
            $this->groupResource->delete($group);
            $this->analyticCollection->deleteByBlockId($group->getGroupId());
            unset($this->groups[$group->getGroupId()]);
        } catch (\Exception $e) {
            if ($group->getGroupId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove group with ID %1. Error: %2',
                        [$group->getGroupId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove group. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($groupId)
    {
        $groupModel = $this->getById($groupId);
        $this->delete($groupModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Mostviewed\Model\ResourceModel\Group\Collection $groupCollection */
        $groupCollection = $this->groupCollectionFactory->create();
        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $groupCollection);
        }
        $searchResults->setTotalCount($groupCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $groupCollection);
        }
        $groupCollection->setCurPage($searchCriteria->getCurrentPage());
        $groupCollection->setPageSize($searchCriteria->getPageSize());
        $groups = [];
        /** @var GroupInterface $group */
        foreach ($groupCollection->getItems() as $group) {
            $groups[] = $this->getById($group->getId());
        }
        $searchResults->setItems($groups);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $groupCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $groupCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $groupCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $groupCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $groupCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $groupCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }

    /**
     * @param $id
     *
     * @return $this
     */
    public function duplicate($id)
    {
        $model = $this->getById($id);
        $model->setId(null);
        $model->setStatus(0);
        $model->setLayoutUpdateId(null);

        $this->save($model);

        return $this;
    }

    /**
     * @return \Amasty\Mostviewed\Api\Data\GroupInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getNew()
    {
        return $this->groupFactory->create();
    }
}
