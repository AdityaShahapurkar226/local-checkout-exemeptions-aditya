<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Finder;

class ItemPool
{
    /**
     * @var Item[]
     */
    private $pool = [];

    /**
     * @var array
     */
    private $poolByProductId = [];

    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var array
     */
    private $availableProductQty = [];

    public function __construct(ItemFactory $itemFactory)
    {
        $this->itemFactory = $itemFactory;
    }

    public function createItem(int $id, int $productId, float $qty): void
    {
        /** @var Item $item */
        $item = $this->itemFactory->create();
        $item->init($id, $productId, $qty);
        $this->addItem($productId, $item);
    }

    public function addItem(int $productId, Item $item): void
    {
        if (isset($this->poolByProductId[$productId])) {
            $this->poolByProductId[$productId][] = $item;
            $this->availableProductQty[$productId] += $item->getQty();
        } else {
            $this->poolByProductId[$productId] = [$item];
            $this->availableProductQty[$productId] = $item->getQty();
        }
        $this->pool[$item->getId()] = $item;
    }

    /**
     * @return Item[]
     */
    public function getItems(): array
    {
        return $this->pool;
    }

    public function decrease(int $itemId, float $qty): void
    {
        if (isset($this->pool[$itemId])) {
            $this->pool[$itemId]->decrease($qty);
            $this->availableProductQty[$this->pool[$itemId]->getProductId()] -= $qty;
        }
    }

    public function retrieveItems(int $productId, float $qty): array
    {
        /** @var Item[] $itemsForRetrieve */
        $itemsForRetrieve = $this->poolByProductId[$productId] ?? [];

        $retrievedItems = [];
        foreach ($itemsForRetrieve as $itemForRetrieve) {
            if ($itemForRetrieve->getQty() > $qty) {
                $retrievedItems[$itemForRetrieve->getId()] = $qty;
                break;
            } else {
                $retrievedItems[$itemForRetrieve->getId()] = $itemForRetrieve->getQty();
                $qty -= $itemForRetrieve->getQty();
            }
        }

        return $retrievedItems;
    }

    public function getQty(int $productId): float
    {
        return $this->availableProductQty[$productId] ?? 0;
    }
}
