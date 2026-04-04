<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Ui\DataProvider\Pack\Listing;

use Amasty\Mostviewed\Api\Data\ConditionalDiscountInterface;
use Amasty\Mostviewed\Model\Pack\ConditionalDiscount\Query\GetListInterface as GetConditionalList;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;

class GetConditionalDiscountsData
{
    /**
     * @var GetConditionalList
     */
    private $getConditionalList;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    public function __construct(
        GetConditionalList $getConditionalList,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->getConditionalList = $getConditionalList;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    public function execute(int $packId): array
    {
        $this->sortOrderBuilder->setField(ConditionalDiscountInterface::NUMBER_ITEMS);
        $this->sortOrderBuilder->setAscendingDirection();
        $orderByNumberItems = $this->sortOrderBuilder->create();

        $this->searchCriteriaBuilder->addFilter(ConditionalDiscountInterface::PACK_ID, $packId);
        $this->searchCriteriaBuilder->addSortOrder($orderByNumberItems);

        $result = [];
        foreach ($this->getConditionalList->execute($this->searchCriteriaBuilder->create()) as $conditionalDiscount) {
            $result[] = [
                'count' => $conditionalDiscount->getNumberItems(),
                'discount' => $conditionalDiscount->getDiscountAmount()
            ];
        }

        return $result;
    }
}
