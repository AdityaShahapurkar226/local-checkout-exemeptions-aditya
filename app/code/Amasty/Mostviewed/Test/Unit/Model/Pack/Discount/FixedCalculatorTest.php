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
use Amasty\Mostviewed\Model\Pack\Discount\FixedCalculator;
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
use Magento\Store\Model\Store;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class FixedCalculatorTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @var FixedCalculator
     */
    private $model;

    protected function setup(): void
    {
        $this->model = $this->getObjectManager()->getObject(FixedCalculator::class);
    }

    /**
     * @covers FixedCalculator::execute
     *
     * @dataProvider executeDataProvider
     *
     * @param float $discountAmount
     * @param float $itemsQty
     * @param float $curs
     * @param array $expectedDiscounts
     *
     * @throws ReflectionException
     */
    public function testExecute(float $discountAmount, float $itemsQty, float $curs, array $expectedDiscounts): void
    {
        $packMock = $this->createMock(PackInterface::class);
        $packMock->expects($this->any())->method('getDiscountType')->willReturn(DiscountType::FIXED);

        $packRepositoryMock = $this->createMock(PackRepositoryInterface::class);
        $packRepositoryMock->expects($this->any())->method('getById')->willReturn($packMock);

        $retrieverMock = $this->createMock(DefaultStrategy::class);
        $retrieverMock->expects($this->any())->method('execute')->willReturn($discountAmount);

        $retrieveDiscountAmountPoolMock = $this->createMock(RetrieveDiscountAmountPool::class);
        $retrieveDiscountAmountPoolMock->expects($this->any())->method('getRetriever')->willReturn(
            $retrieverMock
        );

        $priceCurrencyMock = $this->createMock(PriceCurrencyInterface::class);
        $priceCurrencyMock->expects($this->any())->method('convert')->willReturnCallback(
            function ($amount) use ($curs) {
                return $amount * $curs;
            }
        );

        $this->setProperty($this->model, 'packRepository', $packRepositoryMock);
        $this->setProperty($this->model, 'retrieveDiscountAmountPool', $retrieveDiscountAmountPoolMock);
        $this->setProperty($this->model, 'priceCurrency', $priceCurrencyMock);

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
                2,
                [40, 20]
            ],
            [
                10,
                3,
                2,
                [60, 30]
            ],
            [
                50,
                0.5,
                2,
                [50, 25]
            ],
            [
                50,
                0,
                2,
                [0, 0]
            ],
            [
                0,
                10,
                2,
                [0, 0]
            ]
        ];
    }
}
