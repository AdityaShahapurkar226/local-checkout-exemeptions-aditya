<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Mostviewed\Test\Unit\Block\Widget;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Api\PackRepositoryInterface;
use Amasty\Mostviewed\Block\Widget\PackList;
use Amasty\Mostviewed\Model\Customer\GroupValidator;
use Amasty\Mostviewed\Model\Pack;
use Amasty\Mostviewed\Model\ResourceModel\Product\Collection as ProductCollection;
use Amasty\Mostviewed\Model\ResourceModel\Product\CollectionFactory;
use Amasty\Mostviewed\Test\Unit\Traits;
use Magento\Catalog\Model\Product;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DB\Select;

/**
 * Class PackListTest
 *
 * @see PackList
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PackListTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var PackList
     */
    private $block;

    protected function setup(): void
    {
        $layout = $this->createMock(\Magento\Framework\View\LayoutInterface::class);
        $block = $this->createMock(\Amasty\Mostviewed\Block\Product\BundlePack::class);
        $collectionFactory = $this->createMock(CollectionFactory::class);
        $collection = $this->createMock(ProductCollection::class);
        $store = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $select = $this->createMock(Select::class);
        $packRepository = $this->getMockBuilder(PackRepositoryInterface::class)
            ->setMethods(['getList'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $searchCriteria = $this->createMock(SearchCriteria::class);
        $groupValidator = $this->createMock(GroupValidator::class);
        $pack1 = $this->getObjectManager()->getObject(Pack::class)->setCustomerGroupIds(1)->setPackId(1);
        $pack2 = $this->getObjectManager()->getObject(Pack::class)->setCustomerGroupIds(2)->setPackId(2);

        $layout->expects($this->any())->method('getBlock')->willReturn(null);
        $layout->expects($this->any())->method('createBlock')->willReturn($block);
        $block->expects($this->any())->method('setBundles')->willReturn($block);
        $block->expects($this->any())->method('setProduct')->willReturn($block);
        $block->expects($this->any())->method('toHtml')->willReturn('test');
        $searchCriteriaBuilder->expects($this->any())->method('create')->willReturn($searchCriteria);
        $packRepository->expects($this->any())->method('getList')->willReturn($searchCriteriaBuilder);
        $packRepository->expects($this->any())->method('getPacksByStore')->willReturn([$pack1, $pack2]);
        $groupValidator->expects($this->any())->method('validate')->willReturn(true);
        $collectionFactory->expects($this->any())->method('create')->willReturn($collection);
        $collection->expects($this->any())->method('applyBundleFilter')->willReturn($collection);
        $collection->expects($this->any())->method('addAttributeToSelect')->willReturn($collection);
        $collection->expects($this->any())->method('addStoreFilter')->willReturn($collection);
        $collection->expects($this->any())->method('addMinimalPrice')->willReturn($collection);
        $collection->expects($this->any())->method('addFinalPrice')->willReturn($collection);
        $collection->expects($this->any())->method('addTaxPercents')->willReturn($collection);
        $collection->expects($this->any())->method('addAttributeToSelect')->willReturn($collection);
        $collection->expects($this->any())->method('addUrlRewrite')->willReturn($collection);
        $collection->expects($this->any())->method('getSelect')->willReturn($select);
        $storeManager->expects($this->any())->method('getStore')->willReturn($store);

        $this->block = $this->getObjectManager()->getObject(
            PackList::class,
            [
                'packRepository' => $packRepository,
                'searchCriteriaBuilder' => $searchCriteriaBuilder,
                'groupValidator' => $groupValidator,
                'collectionFactory' => $collectionFactory,
                '_storeManager' => $storeManager,
            ]
        );
        $this->setProperty($this->block, '_layout', $layout);
    }

    /**
     * @covers PackList::renderBundle
     * @dataProvider getMainProductIdDataProvider
     */
    public function testRenderBundle(int $productId, $packProductId, string $result): void
    {
        $product = $this->createMock(Product::class);
        $product->expects($this->any())->method('getId')->willReturn($productId);
        $pack = $this->createMock(PackInterface::class);
        $pack->expects($this->any())->method('getParentIds')->willReturn([$packProductId]);
        $this->setProperty($this->block, 'bundles', [$pack]);
        $this->assertEquals($result, $this->block->renderBundle($product));
    }

    /**
     * @return array
     */
    public function getMainProductIdDataProvider(): array
    {
        return [
            [2, 1, ''],
            [1, 1, 'test']
        ];
    }

    /**
     * @covers PackList::getIdentities
     */
    public function testGetIdentities()
    {
        $pack = $this->createMock(Pack::class);
        $pack->expects($this->any())->method('getPackId')->willReturn(1);
        $pack->expects($this->any())->method('getProductIds')->willReturn('1,2');

        $this->setProperty($this->block, 'bundles', []);
        $this->assertEquals([], $this->block->getIdentities());

        $this->setProperty($this->block, 'bundles', [$pack]);
        $this->assertEquals(
            [Pack::CACHE_TAG . '_1', Product::CACHE_TAG .'_1', Product::CACHE_TAG .'_2'],
            $this->block->getIdentities()
        );
    }

    /**
     * @covers PackList::getBundles
     */
    public function testGetBundles()
    {
        $this->assertArrayHasKey(1, $this->block->getBundles());
        $this->setProperty($this->block, 'bundles', []);
        $this->assertEquals([], $this->block->getBundles());
    }
}
