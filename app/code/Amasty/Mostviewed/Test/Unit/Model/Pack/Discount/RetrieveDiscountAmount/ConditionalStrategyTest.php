<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Test\Unit\Model\Pack\Discount\RetrieveDiscountAmount;

use Amasty\Mostviewed\Api\Data\ConditionalDiscountInterface;
use Amasty\Mostviewed\Api\Data\PackExtensionInterface;
use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Amasty\Mostviewed\Model\Pack\Discount\RetrieveDiscountAmount\ConditionalStrategy;
use Amasty\Mostviewed\Model\Pack\Finder\Result\SimplePack;
use Amasty\Mostviewed\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Mostviewed\Test\Unit\Traits\ReflectionTrait;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class ConditionalStrategyTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @var ConditionalStrategy
     */
    private $model;

    protected function setup(): void
    {
        $this->model = $this->getObjectManager()->getObject(ConditionalStrategy::class);
    }

    /**
     * @covers ConditionalStrategy::execute
     *
     * @dataProvider executeDataProvider
     *
     * @param int $itemsCount
     * @param array|null $conditionalDiscountsData
     * @param float $expectedDiscount
     * @return void
     * @throws ReflectionException
     */
    public function testExecute(int $itemsCount, ?array $conditionalDiscountsData, float $expectedDiscount): void
    {
        if ($conditionalDiscountsData) {
            foreach ($conditionalDiscountsData as $numberItems => $discount) {
                $conditionalDiscountMock = $this->createMock(ConditionalDiscountInterface::class);
                $conditionalDiscountMock->expects($this->any())->method('getNumberItems')->willReturn($numberItems);
                $conditionalDiscountMock->expects($this->any())->method('getDiscountAmount')->willReturn($discount);
                $conditionalDiscounts[] = $conditionalDiscountMock;
            }
        }
        $packExtensionMock = $this->getMockBuilder(PackExtensionInterface::class)
            ->addMethods(['getConditionalDiscounts'])
            ->getMock();
        $packExtensionMock->expects($this->any())->method('getConditionalDiscounts')->willReturn(
            $conditionalDiscounts ?? null
        );

        $packMock = $this->createMock(PackInterface::class);
        $packMock->expects($this->any())->method('getExtensionAttributes')->willReturn(
            $packExtensionMock
        );

        $packRepositoryMock = $this->createMock(PackRepositoryInterface::class);
        $packRepositoryMock->expects($this->any())->method('getById')->willReturn($packMock);

        $this->setProperty($this->model, 'packRepository', $packRepositoryMock);

        $simplePackMock = $this->createMock(SimplePack::class);
        $simplePackMock->expects($this->any())->method('getItemsCount')->willReturn($itemsCount);

        $quoteItemMock = $this->createMock(AbstractItem::class);

        $actualDiscount = $this->model->execute($quoteItemMock, $simplePackMock);
        $this->assertEquals($expectedDiscount, $actualDiscount);
    }

    public function executeDataProvider(): array
    {
        return [
            [
                3,
                [
                    1 => 10.0,
                    2 => 20.0,
                    3 => 30.0
                ],
                30
            ],
            [
                3,
                [
                    1 => 10.0,
                    2 => 20.0,
                    4 => 30.0
                ],
                20
            ],
            [
                3,
                [
                    4 => 30.0,
                    5 => 40.0
                ],
                0
            ],
            [
                3,
                null,
                0
            ],
            [
                3,
                [
                    2 => 60.0,
                    3 => 50.0,
                    5 => 20.0
                ],
                50
            ]
        ];
    }
}
