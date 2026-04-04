<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Pack\Discount;

use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Amasty\Mostviewed\Model\Pack\Discount\RetrieveDiscountAmount\Pool as RetrieveDiscountAmountPool;
use Amasty\Mostviewed\Model\Pack\Finder\Result\SimplePack;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class FixedCalculator implements CalculatorInterface
{
    /**
     * @var RetrieveDiscountAmountPool
     */
    private $retrieveDiscountAmountPool;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var PackRepositoryInterface
     */
    private $packRepository;

    public function __construct(
        RetrieveDiscountAmountPool $retrieveDiscountAmountPool,
        PriceCurrencyInterface $priceCurrency,
        PackRepositoryInterface $packRepository
    ) {
        $this->retrieveDiscountAmountPool = $retrieveDiscountAmountPool;
        $this->priceCurrency = $priceCurrency;
        $this->packRepository = $packRepository;
    }

    public function execute(AbstractItem $item, SimplePack $simplePack): array
    {
        $pack = $this->packRepository->getById($simplePack->getComplexPack()->getPackId());
        $discountAmount = $this->retrieveDiscountAmountPool->getRetriever($pack->getDiscountType())->execute(
            $item,
            $simplePack
        );
        $appliedQty = $simplePack->getItemQty((int) $item->getAmBundleItemId());

        $amount = $appliedQty * $this->priceCurrency->convert($discountAmount, $item->getQuote()->getStore());
        $baseAmount = $appliedQty * $discountAmount;

        return [$amount, $baseAmount];
    }
}
