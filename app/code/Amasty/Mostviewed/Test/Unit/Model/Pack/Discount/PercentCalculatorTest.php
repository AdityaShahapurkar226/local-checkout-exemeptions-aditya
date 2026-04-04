<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Test\Unit\Model\Pack\Discount;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Amasty\Mostviewed\Model\OptionSource\DiscountType;
use Amasty\Mostviewed\Model\Pack\Discount\PercentCalculator;
use Amasty\Mostviewed\Model\Pack\Discount\RetrieveDiscountAmount\DefaultStrategy;
use Amasty\Mostviewed\Model\Pack\Discount\RetrieveDiscountAmount\Pool as RetrieveDiscountAmountPool;
use Amasty\Mostviewed\Model\Pack\Finder\Result\ComplexPack;
use Amasty\Mostviewed\Model\Pack\Finder\Result\SimplePack;
use Amasty\Mostviewed\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Mostviewed\Test\Unit\Traits\ReflectionTrait;
use Magento\Catalog\Model\Product;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\SalesRule\Model\Validator;
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class PercentCalculatorTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @var PercentCalculator
     */
    private $model;

    protected function setup(): void
    {
        $this->model = $this->getObjectManager()->getObject(PercentCalculator::class);
    }

    /**
     * @covers PercentCalculator::execute
     *
     * @dataProvider executeDataProvider
     *
     * @param float $discountAmount
     * @param float $itemsQty
     * @param float $itemPrice
     * @param float $itemBasePrice
     * @param array $expectedDiscounts
     *
     * @throws ReflectionException
     */
    public function testExecute(
        float $discountAmount,
        float $itemsQty,
        float $itemPrice,
        float $itemBasePrice,
        array $expectedDiscounts
    ): void {
        $packMock = $this->createMock(PackInterface::class);
        $packMock->expects($this->any())->method('getDiscountType')->willReturn(
            DiscountType::PERCENTAGE
        );

        $packRepositoryMock = $this->createMock(PackRepositoryInterface::class);
        $packRepositoryMock->expects($this->any())->method('getById')->willReturn($packMock);

        $retrieverMock = $this->createMock(DefaultStrategy::class);
        $retrieverMock->expects($this->any())->method('execute')->willReturn($discountAmount);

        $retrieveDiscountAmountPoolMock = $this->createMock(RetrieveDiscountAmountPool::class);
        $retrieveDiscountAmountPoolMock->expects($this->any())->method('getRetriever')->willReturn(
            $retrieverMock
        );

        $validatorMock = $this->createMock(Validator::class);
        $validatorMock->expects($this->any())->method('getItemPrice')->willReturn($itemPrice);
        $validatorMock->expects($this->any())->method('getItemBasePrice')->willReturn($itemBasePrice);

        $priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);
        $priceCurrencyMock->expects($this->any())->method('round')->willReturnCallback(
            function ($amount) {
                return $amount;
            }
        );

        $this->setProperty($this->model, 'packRepository', $packRepositoryMock);
        $this->setProperty($this->model, 'retrieveDiscountAmountPool', $retrieveDiscountAmountPoolMock);
        $this->setProperty($this->model, 'priceCurrency', $priceCurrencyMock);
        $this->setProperty($this->model, 'validator', $validatorMock);

        $storeMock = $this->createMock(Store::class);
        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->expects($this->any())->method('getStore')->willReturn($storeMock);

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->any())->method('getId')->willReturn(0);

        $quoteItemMock = $this->createMock(AbstractItem::class);
        $quoteItemMock->expects($this->any())->method('getProduct')->willReturn($productMock);
        $quoteItemMock->expects($this->any())->method('getQuote')->willReturn($quoteMock);

        $complexPackMock = $this->createMock(ComplexPack::class);
        $complexPackMock->expects($this->any())->method('getPackId')->willReturn(0);

        $simplePackMock = $this->createMock(SimplePack::class);
        $simplePackMock->expects($this->any())->method('getComplexPack')->willReturn($complexPackMock);
        $simplePackMock->expects($this->any())->method('getItemQty')->willReturn($itemsQty);

        $actualDiscounts = $this->model->execute($quoteItemMock, $simplePackMock);
        $this->assertEquals($expectedDiscounts, $actualDiscounts);
    }

    public function executeDataProvider(): array
    {
        return [
            [
                10,
                2,
                200,
                300,
                [40, 60]
            ],
            [
                10,
                1,
                200,
                300,
                [20, 30]
            ],
            [
                0,
                2,
                200,
                300,
                [0, 0]
            ],
            [
                10,
                0,
                200,
                300,
                [0, 0]
            ]
        ];
    }
}
