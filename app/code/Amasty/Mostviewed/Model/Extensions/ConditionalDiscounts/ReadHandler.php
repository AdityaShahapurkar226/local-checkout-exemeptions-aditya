<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Extensions\ConditionalDiscounts;

use Amasty\Mostviewed\Api\Data\ConditionalDiscountInterface;
use Amasty\Mostviewed\Model\OptionSource\DiscountType;
use Amasty\Mostviewed\Model\Pack;
use Amasty\Mostviewed\Model\Pack\ConditionalDiscount\Query\GetListInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class ReadHandler implements ExtensionInterface
{
    /**
     * @var GetListInterface
     */
    private $getList;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    public function __construct(
        GetListInterface $getList,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->getList = $getList;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * @param Pack|object $entity
     * @param array $arguments
     * @return Pack|bool|object|void
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getDiscountType() === DiscountType::CONDITIONAL) {
            $extensionAttributes = $entity->getExtensionAttributes();
            $extensionAttributes->setConditionalDiscounts($this->getConditionsByRuleId((int) $entity->getId()));
        }

        return $entity;
    }

    /**
     * @param int $packId
     * @return ConditionalDiscountInterface[]
     */
    private function getConditionsByRuleId(int $packId): array
    {
        $this->sortOrderBuilder->setField(ConditionalDiscountInterface::NUMBER_ITEMS);
        $this->sortOrderBuilder->setAscendingDirection();
        $orderByNumberItems = $this->sortOrderBuilder->create();

        $this->searchCriteriaBuilder->addFilter(ConditionalDiscountInterface::PACK_ID, $packId);
        $this->searchCriteriaBuilder->addSortOrder($orderByNumberItems);

        return $this->getList->execute($this->searchCriteriaBuilder->create());
    }
}
