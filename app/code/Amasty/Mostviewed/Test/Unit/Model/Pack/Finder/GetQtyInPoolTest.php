<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Test\Unit\Model\Pack\Finder;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\OptionSource\ApplyCondition;
use Amasty\Mostviewed\Model\OptionSource\DiscountType;
use Amasty\Mostviewed\Model\Pack\Finder\GetQtyInPool;
use Amasty\Mostviewed\Model\Pack\Finder\ItemPool;
use Amasty\Mostviewed\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Mostviewed\Test\Unit\Traits\ReflectionTrait;
use PHPUnit\Framework\TestCase;

class GetQtyInPoolTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @var GetQtyInPool
     */
    private $model;

    protected function setup(): void
    {
        $this->model = $this->getObjectManager()->getObject(GetQtyInPool::class);
    }

    /**
     * @covers GetQtyInPool::execute
     *
     * @dataProvider executeDataProvider
     *
     * @param array $parentIds
     * @param string $childIds
     * @param array $childProductQtyMap
     * @param int $applyCondition
     * @param int $discountType
     * @param array $itemPoolQtyMap
     * @param int $expectedPackQty
     * @return void
     */
    public function testExecute(
        array $parentIds,
        string $childIds,
        array $childProductQtyMap,
        int $applyCondition,
        int $discountType,
        array $itemPoolQtyMap,
        int $expectedPackQty
    ): void {
        $packMock = $this->createMock(PackInterface::class);
        $packMock->expects($this->any())->method('getParentIds')->willReturn($parentIds);
        $packMock->expects($this->any())->method('getProductIds')->willReturn($childIds);
        $packMock->expects($this->any())->method('getApplyCondition')->willReturn($applyCondition);
        $packMock->expects($this->any())->method('getDiscountType')->willReturn($discountType);
        $packMock->expects($this->any())->method('getChildProductQty')->willReturnMap($childProductQtyMap);

        $itemPoolMock = $this->createMock(ItemPool::class);
        $itemPoolMock->expects($this->any())->method('getQty')->willReturnMap($itemPoolQtyMap);

        $this->assertEquals($expectedPackQty, $this->model->execute($packMock, $itemPoolMock));
    }

    public function executeDataProvider(): array
    {
        return [
            [
                [1, 2],
                '3,4',
                [
                    [1, 1],
                    [2, 1],
                    [3, 2],
                    [4, 1]
                ],
                ApplyCondition::ANY_PRODUCTS,
                DiscountType::FIXED,
                [
                    [1, 2],
                    [2, 1],
                    [3, 0],
                    [4, 0]
                ],
                0
            ],
            [
                [1, 2],
                '3,4',
                [
                    [1, 1],
                    [2, 1],
                    [3, 2],
                    [4, 1]
                ],
                ApplyCondition::ANY_PRODUCTS,
                DiscountType::PERCENTAGE,
                [
                    [1, 0],
                    [2, 0],
                    [3, 1],
                    [4, 12]
                ],
                0
            ],
            [
                [1, 2],
                '3,4',
                [
                    [1, 1],
                    [2, 1],
                    [3, 2],
                    [4, 1]
                ],
                ApplyCondition::ALL_PRODUCTS,
                DiscountType::FIXED,
                [
                    [1, 1],
                    [2, 1],
                    [3, 1],
                    [4, 12]
                ],
                0
            ],
            [
                [1, 2],
                '3,4',
                [
                    [1, 1],
                    [2, 1],
                    [3, 1],
                    [4, 1]
                ],
                ApplyCondition::ANY_PRODUCTS,
                DiscountType::PERCENTAGE,
                [
                    [1, 1],
                    [2, 1],
                    [3, 1],
                    [4, 1]
                ],
                2
            ],
            [
                [1, 2],
                '3,4',
                [
                    [1, 1],
                    [2, 1],
                    [3, 2],
                    [4, 1]
                ],
                ApplyCondition::ALL_PRODUCTS,
                DiscountType::FIXED,
                [
                    [1, 2],
                    [2, 1],
                    [3, 2],
                    [4, 2]
                ],
                1
            ],
            [
                [1, 2],
                '3,4',
                [
                    [1, 1],
                    [2, 1],
                    [3, 2],
                    [4, 1]
                ],
                ApplyCondition::ANY_PRODUCTS,
                DiscountType::PERCENTAGE,
                [
                    [1, 2],
                    [2, 1],
                    [3, 2],
                    [4, 2]
                ],
                3
            ],
            [
                [1, 2],
                '3,4',
                [
                    [1, 1],
                    [2, 1],
                    [3, 2],
                    [4, 1]
                ],
                ApplyCondition::ALL_PRODUCTS,
                DiscountType::CONDITIONAL,
                [
                    [1, 2],
                    [2, 1],
                    [3, 2],
                    [4, 2]
                ],
                3
            ]
        ];
    }
}
