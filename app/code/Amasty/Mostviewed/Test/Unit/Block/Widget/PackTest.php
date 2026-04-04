<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Test\Unit\Block\Widget;

use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Amasty\Mostviewed\Block\Widget\Pack;
use Amasty\Mostviewed\Model\Customer\GroupValidator;
use Amasty\Mostviewed\Model\ResourceModel\Pack as PackResource;
use Amasty\Mostviewed\Test\Unit\Traits\ReflectionTrait;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\TestCase;
use ReflectionException;

class PackTest extends TestCase
{
    use ReflectionTrait;

    /**
     * @var Pack
     */
    private $block;

    protected function setUp(): void
    {
        $packRepositoryMock = $this->createMock(PackRepositoryInterface::class);
        $groupValidatorMock = $this->createMock(GroupValidator::class);
        $productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $packResourceMock = $this->createMock(PackResource::class);
        $contextMock = $this->createMock(Context::class);

        $this->block = new Pack(
            $packRepositoryMock,
            $groupValidatorMock,
            $productRepositoryMock,
            $packResourceMock,
            $contextMock
        );

        parent::setUp();
    }

    /**
     * @covers Pack::getMainProductId
     *
     * @dataProvider getMainProductIdDataProvider
     *
     * @param string $selectedProductId
     * @param string[] $availableParentIds
     * @param int|null $expectedResult
     * @throws ReflectionException
     */
    public function testGetMainProductId(
        string $selectedProductId,
        array $availableParentIds,
        ?int $expectedResult
    ): void {
        $packResourceMock = $this->getProperty($this->block, 'packResource');
        $packResourceMock->expects($this->once())->method('getParentIdsByPack')->willReturn($availableParentIds);

        $this->block->setData(Pack::MAIN_PRODUCT_ID, $selectedProductId);
        $actualResult = $this->invokeMethod($this->block, 'getMainProductId');

        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function getMainProductIdDataProvider(): array
    {
        return [
            [
                '',
                ['3', '2', '1'],
                3
            ],
            [
                '4',
                ['1', '2', '3'],
                null
            ],
            [
                '4',
                ['1', '2', '3', '4'],
                4
            ]
        ];
    }
}
