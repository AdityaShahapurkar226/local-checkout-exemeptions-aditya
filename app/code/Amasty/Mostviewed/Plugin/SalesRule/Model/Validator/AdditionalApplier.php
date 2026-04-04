<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\SalesRule\Model\Validator;

use Amasty\Mostviewed\Model\Pack\Cart\Discount\GetPacksForCartItem;
use Amasty\Mostviewed\Model\Pack\QuoteItemProcessor;
use Magento\Quote\Model\Quote\Address;
use Magento\SalesRule\Model\Validator;

/**
 * Fix case when there are no cart price rules
 */
class AdditionalApplier
{
    /**
     * @var GetPacksForCartItem
     */
    private $getPacksForCartItem;

    /**
     * @var QuoteItemProcessor
     */
    private $quoteItemProcessor;

    public function __construct(
        GetPacksForCartItem $getPacksForCartItem,
        QuoteItemProcessor $quoteItemProcessor
    ) {
        $this->getPacksForCartItem = $getPacksForCartItem;
        $this->quoteItemProcessor = $quoteItemProcessor;
    }

    /**
     * @see Validator::initTotals()
     */
    public function afterInitTotals(
        Validator $subject,
        Validator $result,
        array $items,
        Address $address
    ): Validator {
        if (!method_exists($subject, 'getRules') || $subject->getRules($address)->getSize()) {
            return $result;
        }

        foreach ($items as $item) {
            if ($this->quoteItemProcessor->isNotApplicableForItem($item)) {
                continue;
            }

            $itemData = [
                'itemPrice' => $subject->getItemPrice($item),
                'baseItemPrice' => $subject->getItemBasePrice($item),
                'itemOriginalPrice' => $subject->getItemOriginalPrice($item),
                'baseOriginalPrice' => $subject->getItemBaseOriginalPrice($item)
            ];
            $this->quoteItemProcessor->setItemData($itemData);
            $this->quoteItemProcessor->clearItemDiscount($item);
            $appliedPacks = $this->getPacksForCartItem->execute($item);

            foreach ($appliedPacks as $appliedPack) {
                if ($this->quoteItemProcessor->isPackCanBeApplied($appliedPack, $item)) {
                    $this->quoteItemProcessor->applyPackRule($appliedPack, $item);
                    $this->quoteItemProcessor->saveAppliedPackId($appliedPack->getComplexPack()->getPackId());
                }
            }

            if ($appliedPacks) {
                $this->quoteItemProcessor->updateItemDiscountWithPackDiscount($item);
            }
        }

        return $result;
    }
}
