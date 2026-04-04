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
use Magento\SalesRule\Model\Validator;

class PercentCalculator implements CalculatorInterface
{
    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var PackRepositoryInterface
     */
    private $packRepository;

    /**
     * @var RetrieveDiscountAmountPool
     */
    private $retrieveDiscountAmountPool;

    public function __construct(
        RetrieveDiscountAmountPool $retrieveDiscountAmountPool,
        Validator $validator,
        PriceCurrencyInterface $priceCurrency,
        PackRepositoryInterface $packRepository
    ) {
        $this->validator = $validator;
        $this->priceCurrency = $priceCurrency;
        $this->packRepository = $packRepository;
        $this->retrieveDiscountAmountPool = $retrieveDiscountAmountPool;
    }

    public function execute(AbstractItem $item, SimplePack $simplePack): array
    {
        $pack = $this->packRepository->getById($simplePack->getComplexPack()->getPackId());
        $discountAmount = $this->retrieveDiscountAmountPool->getRetriever($pack->getDiscountType())->execute(
            $item,
            $simplePack
        );
        $appliedQty = $simplePack->getItemQty((int) $item->getAmBundleItemId());

        $amount = $appliedQty * $this->validator->getItemPrice($item) * $discountAmount / 100;
        $baseAmount = $appliedQty * $this->validator->getItemBasePrice($item) * $discountAmount / 100;
        $amount = $this->priceCurrency->round($amount);
        $baseAmount = $this->priceCurrency->round($baseAmount);

        return [$amount, $baseAmount];
    }
}
