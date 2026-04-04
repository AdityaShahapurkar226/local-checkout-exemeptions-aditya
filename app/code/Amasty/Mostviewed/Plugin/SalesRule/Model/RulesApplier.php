<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\SalesRule\Model;

use Amasty\Mostviewed\Model\Pack\Cart\Discount\GetPacksForCartItem;
use Amasty\Mostviewed\Model\Pack\QuoteItemProcessor;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class RulesApplier
{
    /**
     * @var AbstractItem
     */
    private $item;

    /**
     * @var \Magento\SalesRule\Model\Validator
     */
    private $validator;

    /**
     * @var GetPacksForCartItem
     */
    private $getPacksForCartItem;

    /**
     * @var QuoteItemProcessor
     */
    private $quoteItemProcessor;

    public function __construct(
        \Magento\SalesRule\Model\Validator $validator,
        GetPacksForCartItem $getPacksForCartItem,
        QuoteItemProcessor $quoteItemProcessor
    ) {
        $this->validator = $validator;
        $this->getPacksForCartItem = $getPacksForCartItem;
        $this->quoteItemProcessor = $quoteItemProcessor;
        $this->_construct();
    }

    protected function _construct(): void
    {
        $this->quoteItemProcessor->clearAppliedPackIds();
    }

    /**
     * @param \Magento\SalesRule\Model\RulesApplier $subject
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\Collection $rules
     * @param bool $skipValidation
     * @param mixed $couponCode
     *
     * @return array
     */
    public function beforeApplyRules($subject, $item, $rules, $skipValidation, $couponCode)
    {
        $this->setItem($item);
        $itemData = [
            'itemPrice' => $this->validator->getItemPrice($item),
            'baseItemPrice' => $this->validator->getItemBasePrice($item),
            'itemOriginalPrice' => $this->validator->getItemOriginalPrice($item),
            'baseOriginalPrice' => $this->validator->getItemBaseOriginalPrice($item)
        ];
        $this->quoteItemProcessor->setItemData($itemData);

        return [$item, $rules, $skipValidation, $couponCode];
    }

    public function afterApplyRules(\Magento\SalesRule\Model\RulesApplier $subject, array $appliedRuleIds): array
    {
        $item = $this->getItem();

        if ($this->quoteItemProcessor->isNotApplicableForItem($item)) {
            return $appliedRuleIds;
        }

        $this->quoteItemProcessor->clearItemDiscount($item);
        $appliedPacks = $this->getPacksForCartItem->execute($this->getItem());

        foreach ($appliedPacks as $appliedPack) {
            if ($this->quoteItemProcessor->isPackCanBeApplied($appliedPack, $item)) {
                $this->quoteItemProcessor->applyPackRule($appliedPack, $item);
                $this->quoteItemProcessor->saveAppliedPackId($appliedPack->getComplexPack()->getPackId());
            }
        }

        if ($appliedPacks) {
            $bundlePackDiscountApplied = $this->quoteItemProcessor->updateItemDiscountWithPackDiscount($item);

            if ($bundlePackDiscountApplied) {
                $appliedRuleIds = [];
            }
        }

        return $appliedRuleIds;
    }

    private function setItem(AbstractItem $item): void
    {
        $this->item = $item;
    }

    private function getItem(): AbstractItem
    {
        return $this->item;
    }
}
