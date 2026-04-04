<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Repository;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Amasty\Mostviewed\Model\PackFactory;
use Amasty\Mostviewed\Model\ResourceModel\Pack as PackResource;
use Amasty\Mostviewed\Model\ResourceModel\Pack\CollectionFactory;
use Amasty\Mostviewed\Model\ResourceModel\Pack\Collection;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\EntityManager\Operation\Read\ReadExtensions;
use Magento\Framework\EntityManager\Operation\Update\UpdateExtensions;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

class PackRepository implements PackRepositoryInterface
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var PackFactory
     */
    private $packFactory;

    /**
     * @var PackResource
     */
    private $packResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $packs;

    /**
     * @var CollectionFactory
     */
    private $packCollectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ReadExtensions
     */
    private $readExtensions;

    /**
     * @var UpdateExtensions
     */
    private $updateExtensions;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        PackFactory $packFactory,
        PackResource $packResource,
        CollectionFactory $packCollectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ReadExtensions $readExtensions,
        UpdateExtensions $updateExtensions,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->packFactory = $packFactory;
        $this->packResource = $packResource;
        $this->packCollectionFactory = $packCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->readExtensions = $readExtensions;
        $this->updateExtensions = $updateExtensions;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * @inheritdoc
     */
    public function save(PackInterface $pack)
    {
        try {
            if ($pack->getPackId()) {
                $pack = $this->getById($pack->getPackId())->addData($pack->getData());
            }
            $this->packResource->save($pack);
            $this->updateExtensions->execute($pack);
            unset($this->packs[$pack->getPackId()]);
        } catch (\Exception $e) {
            if ($pack->getPackId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save pack with ID %1. Error: %2',
                        [$pack->getPackId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new pack. Error: %1', $e->getMessage()));
        }

        return $pack;
    }

    /**
     * @inheritdoc
     */
    public function getById($packId, bool $withExtensions = false): PackInterface
    {
        if (!isset($this->packs[$packId])) {
            /** @var \Amasty\Mostviewed\Model\Pack $pack */
            $pack = $this->packFactory->create();
            $this->packResource->load($pack, $packId);
            if (!$pack->getPackId()) {
                throw new NoSuchEntityException(__('Pack with specified ID "%1" not found.', $packId));
            }
            $this->packs[$packId] = $pack;
            if ($withExtensions) {
                $this->readExtensions->execute($pack);
            }
        }

        return $this->packs[$packId];
    }

    public function getNew(): PackInterface
    {
        return $this->packFactory->create();
    }

    /**
     * @inheritdoc
     */
    public function delete(PackInterface $pack)
    {
        try {
            $this->packResource->delete($pack);
            unset($this->packs[$pack->getPackId()]);
        } catch (\Exception $e) {
            if ($pack->getPackId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove pack with ID %1. Error: %2',
                        [$pack->getPackId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove pack. Error: %1', $e->getMessage()));
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function deleteById($packId)
    {
        $packModel = $this->getById($packId);
        $this->delete($packModel);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function duplicate(PackInterface $pack)
    {
        $parentIds = $this->packResource->getParentIdsByPack($pack->getPackId());
        $pack->setPackId(null);
        $pack->setStatus(0);
        $pack->setCreatedAt(null);
        $pack->setData('parent_product_ids', $parentIds);

        $this->save($pack);
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /** @var \Amasty\Mostviewed\Model\ResourceModel\Pack\Collection $packCollection */
        $packCollection = $this->packCollectionFactory->create();
        $packCollection->addFieldToFilter(PackInterface::STATUS, 1);
        $packCollection->getSelect()->where(
            '(ISNULL(main_table.' . PackInterface::DATE_FROM . ') || main_table.'
            . PackInterface::DATE_FROM . ' <= NOW()) AND '
            . '(ISNULL(main_table.' . PackInterface::DATE_TO
            . ') || main_table.' . PackInterface::DATE_TO . ' >= NOW())'
        );

        // Add filters from root filter group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $packCollection);
        }
        $searchResults->setTotalCount($packCollection->getSize());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($sortOrders, $packCollection);
        }
        $packCollection->setCurPage($searchCriteria->getCurrentPage());
        $packCollection->setPageSize($searchCriteria->getPageSize());
        $packs = [];
        /** @var PackInterface $pack */
        foreach ($packCollection->getItems() as $pack) {
            $packs[] = $this->getById($pack->getPackId(), true);
        }
        $searchResults->setItems($packs);

        return $searchResults;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $packCollection
     *
     * @return void
     */
    private function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $packCollection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $packCollection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Helper function that adds a SortOrder to the collection.
     *
     * @param SortOrder[] $sortOrders
     * @param Collection $packCollection
     *
     * @return void
     */
    private function addOrderToCollection($sortOrders, Collection $packCollection)
    {
        /** @var SortOrder $sortOrder */
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $packCollection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? 'DESC' : 'ASC'
            );
        }
    }

    /**
     * @param array $productIds
     * @param int $storeId
     *
     * @return bool|ExtensibleDataInterface[]|SearchResultsInterface|\Magento\Ui\Api\Data\BookmarkInterface[]
     */
    public function getPacksByParentProductsAndStore($productIds, $storeId)
    {
        if (!$productIds) {
            return false;
        }

        $packIds = $this->packResource->getIdsByProductsAndStore($productIds, $storeId);

        if (!$packIds) {
            return false;
        }

        $packIds = array_unique($packIds);

        $sortOrderByPriority = $this->sortOrderBuilder->setField(PackInterface::PRIORITY)
            ->setDirection(SortOrder::SORT_ASC)
            ->create();
        $sortOrderById = $this->sortOrderBuilder->setField(PackInterface::PACK_ID)
            ->setDirection(SortOrder::SORT_ASC)
            ->create();

        $this->searchCriteriaBuilder
            ->addFilter(PackInterface::PACK_ID, $packIds, 'in')
            ->addFilter(PackInterface::PRODUCT_IDS, '', 'neq')
            ->setSortOrders([$sortOrderByPriority, $sortOrderById])
        ;

        return $this->getList($this->searchCriteriaBuilder->create())->getItems();
    }

    /**
     * @param array $productIds
     * @param int $storeId
     *
     * @return bool|ExtensibleDataInterface[]|SearchResultsInterface|\Magento\Ui\Api\Data\BookmarkInterface[]
     */
    public function getPacksByChildProductsAndStore($productIds, $storeId)
    {
        $packIds = $this->packResource->getIdsByChildProductsAndStore($productIds, $storeId);
        if (!$packIds) {
            return false;
        }

        $packIds = array_unique($packIds);
        $this->searchCriteriaBuilder->addFilter(PackInterface::PACK_ID, $packIds, 'in');

        return $this->getList($this->searchCriteriaBuilder->create())->getItems();
    }

    /**
     * @param int $storeId
     * @return PackInterface[]
     * @throws NoSuchEntityException
     */
    public function getPacksByStore(int $storeId): array
    {
        $packIds = $this->packResource->getIdsByStore($storeId);
        if (!$packIds) {
            return [];
        }

        $packIds = array_unique($packIds);
        $this->searchCriteriaBuilder->addFilter(PackInterface::PACK_ID, $packIds, 'in');

        $sortOrderByPriority = $this->sortOrderBuilder->setField(PackInterface::PRIORITY)
            ->setDirection(SortOrder::SORT_ASC)
            ->create();
        $sortOrderById = $this->sortOrderBuilder->setField(PackInterface::PACK_ID)
            ->setDirection(SortOrder::SORT_ASC)
            ->create();
        $this->searchCriteriaBuilder->setSortOrders([$sortOrderByPriority, $sortOrderById]);

        return $this->getList($this->searchCriteriaBuilder->create())->getItems();
    }

    /**
     * @return PackInterface[]
     */
    public function getLoadedPacks(): array
    {
        return $this->packs;
    }
}
