<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Test\Unit\Model\Pack;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Amasty\Mostviewed\Model\ConfigProvider;
use Amasty\Mostviewed\Model\Pack\Finder\Result\ComplexPack;
use Amasty\Mostviewed\Model\Pack\Finder\Result\SimplePack;
use Amasty\Mostviewed\Model\Pack\QuoteItemProcessor;
use Amasty\Mostviewed\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Mostviewed\Test\Unit\Traits\ReflectionTrait;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class QuoteItemProcessorTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @covers QuoteItemProcessor::isPackCanAppliedForProduct
     *
     * @dataProvider isPackCanBeAppliedDataProvider
     *
     * @param string $childIds
     * @param array $parentIds
     * @param int $applyForParent
     * @param int $cartProductId
     * @param bool $expectedResult
     * @return void
     * @throws ReflectionException
     */
    public function testIsPackCanBeApplied(
        string $childIds,
        array $parentIds,
        int $applyForParent,
        int $cartProductId,
        bool $expectedResult
    ): void {
        $complexPackMock = $this->createMock(ComplexPack::class);

        $simplePackMock = $this->createMock(SimplePack::class);
        $simplePackMock->expects($this->any())->method('getComplexPack')->willReturn($complexPackMock);

        $packMock = $this->createMock(PackInterface::class);
        $packMock->expects($this->any())->method('getParentIds')->willReturn($parentIds);
        $packMock->expects($this->any())->method('getProductIds')->willReturn($childIds);
        $packMock->expects($this->any())->method('getApplyForParent')->willReturn($applyForParent);

        $packRepositoryMock = $this->createMock(PackRepositoryInterface::class);
        $packRepositoryMock->expects($this->any())->method('getById')->willReturn($packMock);

        $productMock = $this->createMock(Product::class);
        $productMock->expects($this->any())->method('getId')->willReturn($cartProductId);

        $quoteItemMock = $this->createMock(QuoteItem::class);
        $quoteItemMock->expects($this->any())->method('getProduct')->willReturn($productMock);

        $session = $this->createMock(Session::class);
        $configProvider = $this->createMock(ConfigProvider::class);
        $model = new QuoteItemProcessor(
            $packRepositoryMock,
            $session,
            $configProvider
        );

        $actualResult = $this->invokeMethod($model, 'isPackCanBeApplied', [$simplePackMock, $quoteItemMock]);

        $this->assertEquals($expectedResult, $actualResult);
    }

    public function isPackCanBeAppliedDataProvider(): array
    {
        return [
            [
                '1,2',
                [3, 4],
                0,
                3,
                false
            ],
            [
                '1,2',
                [3, 4],
                1,
                3,
                true
            ],
            [
                '1,2',
                [3, 4],
                0,
                1,
                true
            ],
            [
                '1,2',
                [3, 4],
                1,
                2,
                true
            ]
        ];
    }
}
