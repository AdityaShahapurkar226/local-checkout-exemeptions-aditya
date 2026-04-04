<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Mostviewed\Test\Unit\Model\Group;

use Amasty\Mostviewed\Model\Group;
use Amasty\Mostviewed\Model\Group\DataProvider;
use Amasty\Mostviewed\Model\ResourceModel\Group\Collection;
use Amasty\Mostviewed\Test\Unit\Traits;
use Magento\Framework\App\Request\DataPersistorInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class DataProviderTest
 *
 * @see DataProvider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DataProviderTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var DataProvider
     */
    private $model;

    protected function setup(): void
    {
        $coreRegistry = $this->createMock(\Magento\Framework\Registry::class);
        $group = $this->createMock(Group::class);
        $group1 = $this->createMock(Group::class);
        $group2 = $this->createMock(Group::class);
        $collection = $this->createMock(Collection::class);
        $dataPersistor = $this->createMock(DataPersistorInterface::class);

        $coreRegistry->expects($this->any())->method('registry')->willReturnOnConsecutiveCalls(null, $group);
        $group->expects($this->any())->method('getGroupId')->willReturn(1);
        $group->expects($this->any())->method('getData')->willReturn(1);
        $group->expects($this->any())->method('getId')->willReturn(1);
        $group1->expects($this->any())->method('getGroupId')->willReturn(5);
        $group1->expects($this->any())->method('getData')->willReturn(5);
        $group2->expects($this->any())->method('getGroupId')->willReturn(6);
        $group2->expects($this->any())->method('getData')->willReturn(6);
        $collection->expects($this->any())->method('getItems')->willReturn([$group1, $group2]);
        $collection->expects($this->any())->method('getNewEmptyItem')->willReturn($group);
        $dataPersistor->expects($this->any())->method('get')->willReturnOnConsecutiveCalls('', 'test');

        $this->model = $this->getObjectManager()->getObject(
            DataProvider::class,
            [
                'coreRegistry' => $coreRegistry,
                'collection' => $collection,
                'dataPersistor' => $dataPersistor,
            ]
        );
    }

    /**
     * @covers DataProvider::getCurrentGroupData
     */
    public function testGetCurrentGroupData()
    {
        $this->invokeMethod($this->model, 'getCurrentGroupData');
        $this->assertEquals(
            [5 => 5, 6 => 6],
            $this->getProperty($this->model, 'loadedData', DataProvider::class)
        );

        $this->invokeMethod($this->model, 'getCurrentGroupData');
        $this->assertEquals(
            [5 => 5, 6 => 6, 1 => 1],
            $this->getProperty($this->model, 'loadedData', DataProvider::class)
        );
    }

    /**
     * @covers DataProvider::getSavedGroupData
     */
    public function testGetSavedGroupData()
    {
        $this->invokeMethod($this->model, 'getSavedGroupData');
        $this->assertNull(
            $this->getProperty($this->model, 'loadedData', DataProvider::class)
        );
        $this->invokeMethod($this->model, 'getSavedGroupData');
        $this->assertEquals(
            [1 => 1],
            $this->getProperty($this->model, 'loadedData', DataProvider::class)
        );
    }
}
