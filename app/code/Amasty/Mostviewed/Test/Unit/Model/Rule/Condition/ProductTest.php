<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Mostviewed\Test\Unit\Model\Rule\Condition;

use Amasty\Mostviewed\Model\Rule\Condition\Product;
use Amasty\Mostviewed\Test\Unit\Traits;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ProductTest
 *
 * @see Product
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Product
     */
    private $model;

    /**
     * @var ProductModel
     */
    private $product;

    /**
     * @var Collection
     */
    private $collection;

    protected function setup(): void
    {
        $this->product = $this->createMock(ProductModel::class);
        $this->collection = $this->createMock(Collection::class);
        $resource = $this->getMockBuilder(\Magento\Framework\Model\ResourceModel\Db\AbstractDb::class)
            ->setMethods(['getAttributeRawValue', 'getAttribute', 'getFrontendInput'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $resource->expects($this->any())->method('getAttributeRawValue')->willReturn('');
        $resource->expects($this->any())->method('getAttribute')->willReturn($resource);
        $resource->expects($this->any())->method('getFrontendInput')->willReturn('select');
        $this->product->expects($this->any())->method('getResource')->willReturn($resource);

        $this->model = $this->createPartialMock(
            Product::class,
            ['getAttributeObject']
        );
        $attribute = $this->createMock(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
        $attribute->expects($this->any())->method('getId')->willReturn(10);
        $this->model->expects($this->any())->method('getAttributeObject')->willReturn($attribute);

    }

    /**
     * @covers Product::addAttributeFilter
     */
    public function testAddAttributeFilter()
    {
        $this->assertTrue($this->invokeMethod(
            $this->model, 'addAttributeFilter',
            [$this->collection, $this->product, 'code', true]
        ));

        $this->product->expects($this->any())->method('getData')->willReturn('test');

        $this->assertTrue($this->invokeMethod(
            $this->model, 'addAttributeFilter',
            [$this->collection, $this->product, 'code', true]
        ));
    }

    /**
     * @covers Product::addCategoryFilter
     */
    public function testAddCategoryFilter()
    {
        $select = $this->createMock(Select::class);
        $productResource = $this->createMock(\Magento\Catalog\Model\ResourceModel\Product::class);
        $configHelper = $this->createMock(\Amasty\Mostviewed\Helper\Config::class);
        $this->invokeMethod($this->model, 'setData', ['config_helper', $configHelper]);
        $this->setProperty($this->model, '_productResource', $productResource);
        $this->collection->expects($this->any())->method('getSelect')->willReturn($select);
        $select->expects($this->any())->method('group')->willReturn($select);

        $this->assertFalse($this->invokeMethod(
            $this->model, 'addCategoryFilter',
            [$this->collection, $this->product, true]
        ));

        $this->product->expects($this->any())->method('getCategoryIds')->willReturn([1, 2]);

        $this->assertTrue($this->invokeMethod(
            $this->model, 'addCategoryFilter',
            [$this->collection, $this->product, true]
        ));
    }

    /**
     * @covers Product::getDefaultOperatorInputByType
     */
    public function testGetDefaultOperatorInputByType()
    {
        $result = [
            'string' => ['==', '!='],
            'numeric' => ['==', '!='],
            'date' => ['==', '!='],
            'select' => ['==', '!='],
            'boolean' => ['==', '!='],
            'multiselect' => ['==', '!='],
            'grid' => ['==', '!='],
            'category' => ['==', '!='],
            'sku' => ['==', '!='],
        ];
        $this->assertEquals($result, $this->model->getDefaultOperatorInputByType());
        $this->setProperty($this->model, '_defaultOperatorInputByType', [1]);
        $this->assertEquals([1], $this->model->getDefaultOperatorInputByType());
    }
}
