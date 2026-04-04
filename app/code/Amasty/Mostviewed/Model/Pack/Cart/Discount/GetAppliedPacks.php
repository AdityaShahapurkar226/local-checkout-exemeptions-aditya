<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Cart\Discount;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Amasty\Mostviewed\Model\Cart\AddProductsByIds;
use Amasty\Mostviewed\Model\ConfigProvider;
use Amasty\Mostviewed\Model\Customer\GroupValidator;
use Amasty\Mostviewed\Model\Pack\Finder\GetItemId;
use Amasty\Mostviewed\Model\Pack\Finder\ItemPool;
use Amasty\Mostviewed\Model\Pack\Finder\ItemPoolFactory;
use Amasty\Mostviewed\Model\Pack\Finder\Result\ComplexPack as ComplexPackResult;
use Amasty\Mostviewed\Model\Pack\Finder\Result\ComplexPackFactory as ComplexPackResultFactory;
use Amasty\Mostviewed\Model\Pack\Finder\RetrievePackFromPool;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class GetAppliedPacks
{
    /**
     * @var array
     */
    private $appliedPacks = [];

    /**
     * @var PackRepositoryInterface
     */
    private $packRepository;

    /**
     * @var GroupValidator
     */
    private $groupValidator;

    /**
     * @var ItemPoolFactory
     */
    private $itemPoolFactory;

    /**
     * @var RetrievePackFromPool
     */
    private $retrievePackFromPool;

    /**
     * @var ComplexPackResultFactory
     */
    private $complexPackResultFactory;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetItemId
     */
    private $getItemId;

    public function __construct(
        PackRepositoryInterface $packRepository,
        GroupValidator $groupValidator,
        RetrievePackFromPool $retrievePackFromPool,
        ItemPoolFactory $itemPoolFactory,
        ComplexPackResultFactory $complexPackResultFactory,
        ConfigProvider $configProvider,
        GetItemId $getItemId
    ) {
        $this->packRepository = $packRepository;
        $this->groupValidator = $groupValidator;
        $this->retrievePackFromPool = $retrievePackFromPool;
        $this->itemPoolFactory = $itemPoolFactory;
        $this->complexPackResultFactory = $complexPackResultFactory;
        $this->configProvider = $configProvider;
        $this->getItemId = $getItemId;
    }

    /**
     * @param CartInterface $quote
     * @return ComplexPackResult[]
     */
    public function execute(CartInterface $quote): array
    {
        if (!isset($this->appliedPacks[$quote->getId()])) {
            $itemPools = $this->convertQuoteToPool($quote);
            $this->appliedPacks[$quote->getId()] = [];
            foreach ($itemPools as $packId => $itemPool) {
                $appliedPacks = $this->resolvePacks((int) $packId, $itemPool, (int) $quote->getStoreId());
                if ($appliedPacks) {
                    array_push($this->appliedPacks[$quote->getId()], ...$appliedPacks);
                }
            }
        }

        return $this->appliedPacks[$quote->getId()];
    }

    /**
     * @param int|null $packId
     * @param ItemPool $itemPool
     * @param int $storeId
     * @return ComplexPackResult[]
     */
    private function resolvePacks(?int $packId, ItemPool $itemPool, int $storeId): array
    {
        $appliedPacks = [];

        if ($packId) {
            $packs = [$this->packRepository->getById($packId, true)];
        } else {
            $packs = $this->findAllAvailablePacks($itemPool, $storeId);
        }

        foreach ($packs as $pack) {
            /** @var ComplexPackResult $complexPackResult */
            $complexPackResult = $this->complexPackResultFactory->create();
            $packResults = $this->retrievePackFromPool->execute($pack, $itemPool);
            $complexPackResult->setPacks($packResults);
            $complexPackResult->setPackId((int) $pack->getPackId());
            if ($complexPackResult->getPackQty()) {
                $appliedPacks[] = $complexPackResult;
            }
        }

        return $appliedPacks;
    }

    /**
     * @param ItemPool $itemPool
     * @param int $storeId
     * @return PackInterface[]
     */
    private function findAllAvailablePacks(ItemPool $itemPool, int $storeId): array
    {
        $allProductIds = [];
        foreach ($itemPool->getItems() as $item) {
            $allProductIds[] = $item->getProductId();
        }
        $allProductIds = array_unique($allProductIds);

        $packsAsChild = $this->packRepository->getPacksByChildProductsAndStore($allProductIds, $storeId) ?: [];
        $packsAsParent = $this->packRepository->getPacksByParentProductsAndStore($allProductIds, $storeId) ?: [];

        /** @var PackInterface[] $packsMerged */
        $packsMerged = [];
        foreach (array_merge($packsAsChild, $packsAsParent) as $pack) {
            if ($this->groupValidator->validate($pack)) {
                $packsMerged[$pack->getPackId()] = $pack;
            }
        }
        usort($packsMerged, function ($packA, $packB) {
            return $packA->getPackId() <=> $packB->getPackId();
        });

        return $packsMerged;
    }

    /**
     * @param CartInterface $quote
     * @return ItemPool[]
     */
    private function convertQuoteToPool(CartInterface $quote): array
    {
        $itemPools = [];

        foreach ($quote->getAllAddresses() as $address) {
            foreach ($address->getAllItems() as $quoteItem) {
                if ($this->configProvider->isProductsCanBeAddedSeparately()
                    || $quoteItem->getOptionByCode(AddProductsByIds::BUNDLE_PACK_OPTION_CODE)
                ) {
                    if ($bundlePackIdOption = $quoteItem->getOptionByCode(AddProductsByIds::BUNDLE_PACK_ID_OPTION)) {
                        $packId = $bundlePackIdOption->getValue();
                    } else {
                        $packId = null;
                    }

                    /** @var ItemPool $itemPool */
                    if (isset($itemPools[$packId])) {
                        $itemPool = $itemPools[$packId];
                    } else {
                        $itemPool = $this->itemPoolFactory->create();
                        $itemPools[$packId] = $itemPool;
                    }
                    $this->updateQuoteItem($quoteItem);
                    $itemPool->createItem(
                        (int) $quoteItem->getAmBundleItemId(),
                        (int) $quoteItem->getProduct()->getId(),
                        (float) $quoteItem->getTotalQty()
                    );
                }
            }
        }

        return $itemPools;
    }

    private function updateQuoteItem(AbstractItem $item): void
    {
        // we need unique identifier for quote item,
        // but quote item id is null on first totals collect after adding product(bundle pack) in cart
        $item->setAmBundleItemId($item->getId() ?? $this->getItemId->execute());
    }
}
