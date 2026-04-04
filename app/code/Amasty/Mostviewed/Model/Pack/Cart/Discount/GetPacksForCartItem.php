<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Cart\Discount;

use Amasty\Mostviewed\Model\Pack\Finder\Result\SimplePack;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class GetPacksForCartItem
{
    /**
     * @var GetAppliedPacks
     */
    private $getAppliedPacks;

    public function __construct(
        GetAppliedPacks $getAppliedPacks
    ) {
        $this->getAppliedPacks = $getAppliedPacks;
    }

    /**
     * Return packs , which contain given item.
     *
     * @param AbstractItem $item
     * @return SimplePack[]
     */
    public function execute(AbstractItem $item): array
    {
        $packsForItem = [];
        foreach ($this->getAppliedPacks->execute($item->getQuote()) as $appliedPack) {
            foreach ($appliedPack->getPacks() as $simplePack) {
                if ($this->isPackCanApplied($simplePack, $item)) {
                    $packsForItem[] = $simplePack;
                }
            }
        }

        return $packsForItem;
    }

    private function isPackCanApplied(SimplePack $simplePack, AbstractItem $item): bool
    {
        return in_array($item->getAmBundleItemId(), array_keys($simplePack->getItems()));
    }
}
