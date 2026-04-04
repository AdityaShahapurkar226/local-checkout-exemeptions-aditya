<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack;

use Amasty\Mostviewed\Model\Cart\AddProductsByIds;
use Amasty\Mostviewed\Model\ConfigProvider;
use Amasty\Mostviewed\Model\OptionSource\DiscountType;
use Amasty\Mostviewed\Model\Pack\Discount\CalculatorInterface;
use Amasty\Mostviewed\Model\Pack\Finder\Result\SimplePack;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class QuoteItemProcessor
{
    /**
     * @var array
     */
    private $itemData = [];

    /**
     * @var \Amasty\Mostviewed\Api\PackRepositoryInterface
     */
    private $packRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var CalculatorInterface[]
     */
    private $calculators;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        \Amasty\Mostviewed\Api\PackRepositoryInterface $packRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        ConfigProvider $configProvider,
        array $calculators = []
    ) {
        $this->packRepository = $packRepository;
        $this->checkoutSession = $checkoutSession;
        $this->calculators = $calculators;
        $this->configProvider = $configProvider;
    }

    public function isNotApplicableForItem(AbstractItem $item): bool
    {
        return !$this->configProvider->isProductsCanBeAddedSeparately()
            && $item->getOptionByCode(AddProductsByIds::BUNDLE_PACK_OPTION_CODE) === null;
    }

    public function isPackCanBeApplied(SimplePack $simplePack, AbstractItem $item): bool
    {
        $pack = $this->packRepository->getById($simplePack->getComplexPack()->getPackId(), true);
        $childIds = explode(',', $pack->getProductIds());
        $productId = (int)$item->getProduct()->getId();

        return in_array($productId, $childIds)
            || (
                in_array($productId, $pack->getParentIds())
                && ($pack->getApplyForParent() || $pack->getDiscountType() === DiscountType::CONDITIONAL)
            );
    }

    public function applyPackRule(SimplePack $simplePack, AbstractItem $item): void
    {
        if (!$this->itemData) {
            return;
        }

        $pack = $this->packRepository->getById($simplePack->getComplexPack()->getPackId(), true);
        [$amountPerPack, $baseAmountPerPack] = $this->calculators[$pack->getDiscountType()]->execute(
            $item,
            $simplePack
        );
        $qty = $simplePack->getItemQty((int) $item->getAmBundleItemId());

        $amountPerPack = min($amountPerPack, $qty * $this->itemData['itemPrice']);
        $baseAmountPerPack = min($baseAmountPerPack, $qty * $this->itemData['baseItemPrice']);

        $amount = $amountPerPack + $item->getAmDiscountAmount() ?: 0;
        $baseAmount = $baseAmountPerPack + $item->getAmBaseDiscountAmount() ?: 0;

        if ($baseAmount) {
            $item->setAmDiscountAmount($amount);
            $item->setAmBaseDiscountAmount($baseAmount);
            $discounts = $item->getAmDiscounts() ?: [];
            $discounts[$simplePack->getId()] = [
                'amount' => $amountPerPack,
                'base_amount' => $baseAmountPerPack
            ];
            $item->setAmDiscounts($discounts);
        }
    }

    public function setItemData(array $itemData): void
    {
        $this->itemData = $itemData;
    }

    public function saveAppliedPackId(int $packId): void
    {
        $bundlePackIds = $this->checkoutSession->getAppliedPackIds() ?: [];

        if (!in_array($packId, $bundlePackIds)) {
            $bundlePackIds[] = $packId;
        }

        $this->checkoutSession->setAppliedPackIds($bundlePackIds);
    }

    public function clearAppliedPackIds(): void
    {
        $this->checkoutSession->setAppliedPackIds([]);
    }

    public function clearItemDiscount(AbstractItem $item): void
    {
        $item->setAmDiscountAmount(0);
        $item->setAmBaseDiscountAmount(0);
    }

    /**
     * Return true if bundle pack discount applied instead of magento cart price rules.
     */
    public function updateItemDiscountWithPackDiscount(AbstractItem $item): bool
    {
        $amDiscountAmount = $item->getAmDiscountAmount();
        $amBaseDiscountAmount = $item->getAmBaseDiscountAmount();

        if ($this->configProvider->isApplyCartRule() && $item->getDiscountAmount() > $amDiscountAmount) {
            $bundlePackDiscountApplied = false;
        } else {
            $item->setDiscountAmount($amDiscountAmount);
            $item->setBaseDiscountAmount($amBaseDiscountAmount);
            $bundlePackDiscountApplied = true;
        }

        return $bundlePackDiscountApplied;
    }
}
