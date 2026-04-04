<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Mostviewed\Test\Unit\Model\Pack;

use Amasty\Mostviewed\Api\Data\PackExtensionInterface;
use Amasty\Mostviewed\Model\Backend\Pack\Registry;
use Amasty\Mostviewed\Model\Pack;
use Amasty\Mostviewed\Model\Pack\DataProvider as DataProviderModel;
use Amasty\Mostviewed\Test\Unit\Traits;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;

/**
 * Class DataProviderTest
 *
 * @see DataProviderModel
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var DataProviderModel
     */
    private $model;

    /**
     * @var PackExtensionInterface
     */
    private $extensionAttributes;

    protected function setup(): void
    {
        $collection = $this->createMock(\Amasty\Mostviewed\Model\ResourceModel\Pack\Collection::class);
        $packRegistry = $this->createMock(Registry::class);
        $pack = $this->createMock(Pack::class);

        $this->extensionAttributes = $this->getMockBuilder(PackExtensionInterface::class)
            ->addMethods(['getStores', 'setStores', 'getConditionalDiscounts', 'setConditionalDiscounts'])
            ->getMock();
        $dataPersistor = $this->createMock(DataPersistorInterface::class);
        $pool = $this->createMock(PoolInterface::class);
        $productCollection = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class)
            ->setMethods(['create', 'addIdFilter', 'addAttributeToSelect', 'getItems'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $packRegistry->expects($this->any())->method('get')->willReturnOnConsecutiveCalls($pack, $pack);
        $pack->expects($this->any())->method('getPackId')->willReturn(1);
        $pack->expects($this->any())->method('getExtensionAttributes')->willReturn($this->extensionAttributes);
        $this->extensionAttributes->expects($this->any())->method('getStores')->willReturn(null);
        $dataPersistor->expects($this->any())->method('get')->willReturnOnConsecutiveCalls(null, $pack);
        $productCollection->expects($this->any())->method('create')->willReturn($productCollection);
        $productCollection->expects($this->any())->method('addIdFilter')->willReturn($productCollection);
        $productCollection->expects($this->any())->method('addAttributeToSelect')->willReturn($productCollection);
        $productCollection->expects($this->any())->method('getItems')->willReturn([]);
        $pool->expects($this->any())->method('getModifiersInstances')->willReturn([]);

        $this->model = $this->getObjectManager()->getObject(
            DataProviderModel::class,
            [
                'collection' => $collection,
                'packRegistry' => $packRegistry,
                'productCollectionFactory' => $productCollection,
                'pool' => $pool
            ]
        );
    }

    /**
     * @covers DataProviderModel::getData
     */
    public function testGetData()
    {
        $this->assertEquals([1 => []], $this->model->getData());
        $this->assertEquals([1 => []], $this->model->getData());
    }

    /**
     * @covers DataProviderModel::convertProductsData
     *
     * @dataProvider convertProductsDataDataProvider
     *
     * @param array $packData
     * @param array $expectedResult
     * @return void
     * @throws \ReflectionException
     */
    public function testConvertProductsData(array $packData, array $expectedResult): void
    {
        $pack = $this->createMock(Pack::class);
        $pack->expects($this->any())->method('getExtensionAttributes')->willReturn($this->extensionAttributes);
        $pack->expects($this->any())->method('getData')->willReturn($packData);

        $this->assertEquals(
            $expectedResult,
            $this->invokeMethod($this->model, 'convertProductsData', [$pack])
        );
    }

    public function convertProductsDataDataProvider(): array
    {
        return [
            [
                [], []
            ],
            [
                ['product_ids' => 'test'],
                ['product_ids' =>['child_products_container' => []]],
            ],
            [
                ['parent_ids' => 'test'],
                ['parent_ids' =>['parent_products_container' => []]]
            ]
        ];
    }
}
