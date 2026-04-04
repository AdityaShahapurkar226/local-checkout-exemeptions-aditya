<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Test\Unit\Model\Pack\Discount\RetrieveDiscountAmount;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Amasty\Mostviewed\Model\Pack\Discount\RetrieveDiscountAmount\DefaultStrategy;
use Amasty\Mostviewed\Model\Pack\Finder\Result\ComplexPack;
use Amasty\Mostviewed\Model\Pack\Finder\Result\SimplePack;
use Amasty\Mostviewed\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Mostviewed\Test\Unit\Traits\ReflectionTrait;
use Magento\Catalog\Model\Product;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class DefaultStrategyTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @var DefaultStrategy
     */
    private $model;

    protected function setup(): void
    {
        $this->model = $this->getObjectManager()->getObject(DefaultStrategy::class);
    }

    /**
     * @covers DefaultStrategy::execute
     *
     * @dataProvider executeDataProvider
     *
     * @param int $productId
     * @param float|null $packDiscountAmount
     * @param array $childDiscounts
     * @param float $expectedDiscount
     *
     * @throws ReflectionException
     */
    public function testExecute(
        int $productId,
        ?float $packDiscountAmount,
        array $childDiscounts,
        float $expectedDiscount
    ): void {
        $packMock = $this->createMock(PackInterface::class);
        $packMock->expects($this->any())->method('getDiscountAmount')->willReturn($packDiscountAmount);
        $packMock->expects($this->any())->method('getChildProductDiscount')->willReturnMap($childDiscounts);

        $packRepositoryMock = $this->createMock(PackRepositoryInterface::class);
        $packRepositoryMock->expects($this->any())->method('getById')->willReturn($packMock);

        $this->setProperty($this->model, 'packRepository', $packRepositoryMock);

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->any())->method('getId')->willReturn($productId);

        $quoteItemMock = $this->createMock(AbstractItem::class);
        $quoteItemMock->expects($this->any())->method('getProduct')->willReturn($productMock);

        $complexPackMock = $this->createMock(ComplexPack::class);
        $complexPackMock->expects($this->any())->method('getPackId')->willReturn(0);

        $simplePackMock = $this->createMock(SimplePack::class);
        $simplePackMock->expects($this->any())->method('getComplexPack')->willReturn($complexPackMock);

        $actualDiscount = $this->model->execute($quoteItemMock, $simplePackMock);
        $this->assertEquals($expectedDiscount, $actualDiscount);
    }

    public function executeDataProvider(): array
    {
        return [
            [
                1,
                10,
                [
                    [1, 15],
                    [2, null]
                ],
                15
            ],
            [
                2,
                10,
                [
                    [1, 15],
                    [2, null]
                ],
                10
            ],
            [
                2,
                null,
                [
                    [1, 15],
                    [2, null]
                ],
                0
            ]
        ];
    }
}
