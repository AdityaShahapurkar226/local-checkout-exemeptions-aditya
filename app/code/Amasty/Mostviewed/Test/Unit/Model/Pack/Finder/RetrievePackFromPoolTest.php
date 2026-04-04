<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Test\Unit\Model\Pack\Finder;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Model\Pack\Finder\GetQtyInPool;
use Amasty\Mostviewed\Model\Pack\Finder\ItemPool;
use Amasty\Mostviewed\Model\Pack\Finder\Result\SimplePack;
use Amasty\Mostviewed\Model\Pack\Finder\Result\SimplePackFactory;
use Amasty\Mostviewed\Model\Pack\Finder\RetrievePackFromPool;
use Amasty\Mostviewed\Test\Unit\Traits\ObjectManagerTrait;
use Amasty\Mostviewed\Test\Unit\Traits\ReflectionTrait;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class RetrievePackFromPoolTest extends TestCase
{
    use ObjectManagerTrait;
    use ReflectionTrait;

    /**
     * @var RetrievePackFromPool
     */
    private $model;

    protected function setup(): void
    {
        $this->model = $this->getObjectManager()->getObject(RetrievePackFromPool::class);
    }

    /**
     * @covers ConditionalStrategy::execute
     *
     * @dataProvider executeDataProvider
     *
     * @param int $packQty
     * @param array $itemQtyMap
     * @param array $parentIds
     * @param string $childIds
     * @param array $childQtyMap
     * @param array $itemQtyToCheck
     * @return void
     * @throws ReflectionException
     */
    public function testExecute(
        int $packQty,
        array $itemQtyMap,
        array $parentIds,
        string $childIds,
        array $childQtyMap,
        array $itemQtyToCheck
    ): void {
        $packResultFactoryMock = $this->createMock(SimplePackFactory::class);
        $packResultFactoryMock->expects($this->any())->method('create')->willReturnCallback(function () {
            return $this->getObjectManager()->getObject(SimplePack::class);
        });

        $getQtyInPoolMock = $this->createMock(GetQtyInPool::class);
        $getQtyInPoolMock->expects($this->any())->method('execute')->willReturn($packQty);

        $this->setProperty($this->model, 'getQtyInPool', $getQtyInPoolMock);
        $this->setProperty($this->model, 'packResultFactory', $packResultFactoryMock);

        $itemPoolMock = $this->createMock(ItemPool::class);
        $itemPoolMock->expects($this->any())->method('getQty')->willReturnCallback(
            function ($itemId) use (&$itemQtyMap) {
                return $itemQtyMap[$itemId];
            }
        );
        $itemPoolMock->expects($this->any())->method('decrease')->willReturnCallback(
            function ($itemId, $qtyToDecrease) use (&$itemQtyMap) {
                return $itemQtyMap[$itemId] -= $qtyToDecrease;
            }
        );
        $itemPoolMock->expects($this->any())->method('retrieveItems')->willReturnCallback(
            function ($itemId, $itemQty) {
                return [$itemId => $itemQty];
            }
        );

        $packMock = $this->createMock(PackInterface::class);
        $packMock->expects($this->any())->method('getParentIds')->willReturn($parentIds);
        $packMock->expects($this->any())->method('getProductIds')->willReturn($childIds);
        $packMock->expects($this->any())->method('getChildProductQty')->willReturnMap($childQtyMap);

        $packResults = $this->model->execute($packMock, $itemPoolMock);

        foreach ($packResults as $index => $packResult) {
            foreach ($itemQtyToCheck[$index] as $itemId => $itemQty) {
                $this->assertEquals($itemQty, $packResult->getItemQty($itemId));
            }
            $this->assertEquals(count($itemQtyToCheck[$index]), $packResult->getItemsCount());
        }
    }

    public function executeDataProvider(): array
    {
        return [
            [
                1,
                [
                    1 => 1,
                    2 => 1,
                    3 => 1
                ],
                [1],
                '2,3',
                [
                    [2, 1],
                    [3, 1]
                ],
                [
                    [
                        1 => 1,
                        2 => 1,
                        3 => 1
                    ]
                ]
            ],
            [
                1,
                [
                    1 => 1,
                    2 => 1,
                    3 =>1
                ],
                [1],
                '2,3',
                [
                    [2, 1],
                    [3, 1]
                ],
                [
                    [
                        1 => 1,
                        2 => 1,
                        3 => 1
                    ]
                ]
            ],
            [
                2,
                [
                    1 => 3,
                    2 => 3,
                    3 => 2
                ],
                [1],
                '2,3',
                [
                    [2, 1],
                    [3, 1]
                ],
                [
                    [
                        1 => 2,
                        2 => 2,
                        3 => 2
                    ],
                    [
                        1 => 1,
                        2 => 1
                    ]
                ]
            ],
            [
                2,
                [
                    1 => 3,
                    2 => 3,
                    3 => 4
                ],
                [1],
                '2,3',
                [
                    [2, 2],
                    [3, 2]
                ],
                [
                    [
                        1 => 1,
                        2 => 2,
                        3 => 2
                    ],
                    [
                        1 => 1,
                        3 => 2
                    ]
                ]
            ],
            [
                3,
                [
                    1 => 3,
                    2 => 3,
                    3 => 6,
                    4 => 1
                ],
                [1, 4],
                '2,3',
                [
                    [2, 2],
                    [3, 2]
                ],
                [
                    [
                        1 => 1,
                        2 => 2,
                        3 => 2
                    ],
                    [
                        1 => 2,
                        3 => 4
                    ]
                ]
            ],
            [
                3,
                [
                    1 => 2,
                    2 => 3,
                    3 => 6,
                    4 => 1
                ],
                [1, 4],
                '2,3',
                [
                    [2, 2],
                    [3, 2]
                ],
                [
                    [
                        1 => 1,
                        2 => 2,
                        3 => 2
                    ],
                    [
                        1 => 1,
                        3 => 2
                    ],
                    [
                        4 => 1,
                        3 => 2
                    ]
                ]
            ],
            [
                3,
                [
                    4 => 2,
                    1 => 1,
                    2 => 3,
                    3 => 6,
                    7 => 10
                ],
                [4, 1],
                '2,3',
                [
                    [2, 2],
                    [3, 2]
                ],
                [
                    [
                        4 => 1,
                        2 => 2,
                        3 => 2
                    ],
                    [
                        4 => 1,
                        3 => 2
                    ],
                    [
                        1 => 1,
                        3 => 2
                    ]
                ]
            ]
        ];
    }
}
