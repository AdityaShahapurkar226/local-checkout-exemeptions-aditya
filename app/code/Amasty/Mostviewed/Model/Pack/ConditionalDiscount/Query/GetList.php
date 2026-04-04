<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\ConditionalDiscount\Query;

use Amasty\Mostviewed\Api\Data\ConditionalDiscountInterface;
use Amasty\Mostviewed\Model\Pack\ConditionalDiscount\Registry;
use Amasty\Mostviewed\Model\ResourceModel\ConditionalDiscount\Collection;
use Amasty\Mostviewed\Model\ResourceModel\ConditionalDiscount\CollectionFactory;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;

class GetList implements GetListInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(CollectionFactory $collectionFactory, Registry $registry)
    {
        $this->collectionFactory = $collectionFactory;
        $this->registry = $registry;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return ConditionalDiscountInterface[]
     */
    public function execute(SearchCriteriaInterface $searchCriteria): array
    {
        /** @var Collection $ */
        $collection = $this->collectionFactory->create();

        $this->addFilterGroupToCollection($collection, $searchCriteria->getFilterGroups());
        $sortOrders = $searchCriteria->getSortOrders();
        if ($sortOrders) {
            $this->addOrderToCollection($collection, $sortOrders);
        }

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());

        $conditionalDiscounts = [];
        /** @var ConditionalDiscountInterface $range */
        foreach ($collection->getItems() as $conditionalDiscount) {
            $conditionalDiscounts[] = $conditionalDiscount;
            $this->registry->save($conditionalDiscount);
        }

        return $conditionalDiscounts;
    }

    /**
     * @param Collection $collection
     * @param FilterGroup[] $filterGroups
     * @return void
     */
    private function addFilterGroupToCollection(Collection $collection, array $filterGroups): void
    {
        foreach ($filterGroups as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
    }

    /**
     * @param Collection $collection
     * @param SortOrder[] $sortOrders
     * @return void
     */
    private function addOrderToCollection(Collection $collection, array $sortOrders): void
    {
        foreach ($sortOrders as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_DESC) ? SortOrder::SORT_DESC : SortOrder::SORT_ASC
            );
        }
    }
}
