<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Finder;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\OptionSource\ApplyCondition;
use Amasty\Mostviewed\Model\OptionSource\DiscountType;

class GetQtyInPool
{
    public function execute(PackInterface $pack, ItemPool $itemPool): int
    {
        $parentIds = $pack->getParentIds();

        $parentPackQty = 0;
        foreach ($parentIds as $parentId) {
            $parentPackQty += $itemPool->getQty((int) $parentId);
        }

        if (!$parentPackQty) {
            return 0;
        }

        $childProductIds = explode(',', $pack->getProductIds());
        $childPackQty = 0;
        foreach ($childProductIds as $childProductId) {
            $childProductId = (int) $childProductId;
            $packQty = floor(
                $itemPool->getQty((int) $childProductId) / $pack->getChildProductQty($childProductId)
            );
            $availablePacksQty[] = $packQty;
            $childPackQty += $packQty;
        }

        if ($pack->getApplyCondition() === ApplyCondition::ALL_PRODUCTS
            && $pack->getDiscountType() !== DiscountType::CONDITIONAL
        ) {
            $availablePacksQty[] = $parentPackQty;
            $packQty = min($availablePacksQty);
        } else {
            $packQty = min($parentPackQty, $childPackQty);
        }

        return (int) floor($packQty);
    }
}
