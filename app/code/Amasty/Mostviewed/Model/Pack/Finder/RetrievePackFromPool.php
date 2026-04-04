<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Finder;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\Pack\Finder\Result\SimplePack as PackResult;
use Amasty\Mostviewed\Model\Pack\Finder\Result\SimplePackFactory as PackResultFactory;

class RetrievePackFromPool
{
    /**
     * @var PackResultFactory
     */
    private $packResultFactory;

    /**
     * @var GetQtyInPool
     */
    private $getQtyInPool;

    public function __construct(PackResultFactory $packResultFactory, GetQtyInPool $getQtyInPool)
    {
        $this->packResultFactory = $packResultFactory;
        $this->getQtyInPool = $getQtyInPool;
    }

    /**
     * @param PackInterface $pack
     * @param ItemPool $itemPool
     * @return PackResult[]
     */
    public function execute(PackInterface $pack, ItemPool $itemPool): array
    {
        $packResults = [];

        $parentIds = $pack->getParentIds();
        $packQty = $this->getQtyInPool->execute($pack, $itemPool);
        $parentId = (int) reset($parentIds);
        $prevItemsCount = 0;
        while ($packQty > 0 && $parentId) {
            $leftQty = $itemPool->getQty($parentId);
            $prevItemsCount = 0;
            while ($packQty > 0 && $leftQty > 0) {
                $currentItemsCount = 1;
                $childIdsForPack = [];
                $childIds = explode(',', $pack->getProductIds());
                foreach ($childIds as $childId) {
                    $childId = (int) $childId;
                    $childQtyForOnePack = $pack->getChildProductQty($childId);
                    if ($itemPool->getQty($childId) >= $childQtyForOnePack) {
                        $currentItemsCount++;
                        $childIdsForPack[$childId] = $childQtyForOnePack;
                    }
                }
                if (!$childIdsForPack) {
                    break 2;
                }
                if ($currentItemsCount !== $prevItemsCount) {
                    $prevItemsCount = $currentItemsCount;
                    /** @var PackResult $packResult */
                    $packResult = $this->packResultFactory->create();
                    $packResults[] = $packResult;
                }
                foreach ($childIdsForPack as $childId => $childQtyForOnePack) {
                    $items = $itemPool->retrieveItems($childId, $childQtyForOnePack);
                    foreach ($items as $itemId => $itemQtyForDecrease) {
                        $packResult->addItem($itemId, $itemQtyForDecrease);
                        $itemPool->decrease($itemId, $itemQtyForDecrease);
                    }
                }
                $items = $itemPool->retrieveItems($parentId, 1);
                foreach ($items as $itemId => $itemQtyForDecrease) {
                    $packResult->addItem($itemId, $itemQtyForDecrease);
                    $itemPool->decrease($itemId, $itemQtyForDecrease);
                }
                $packResult->setPackQty($packResult->getPackQty() + 1);
                $leftQty--;
                $packQty--;
            }
            $parentId = (int) next($parentIds);
        }

        return $packResults;
    }
}
