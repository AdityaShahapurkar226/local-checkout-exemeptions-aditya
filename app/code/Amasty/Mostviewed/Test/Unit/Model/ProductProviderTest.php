<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Mostviewed\Test\Unit\Model;

use Amasty\Mostviewed\Helper\Config as MostviewedHelperConfig;
use Amasty\Mostviewed\Model\Di\Wrapper;
use Amasty\Mostviewed\Model\OptionSource\Sortby;
use Amasty\Mostviewed\Model\ProductProvider;
use Amasty\Mostviewed\Model\Repository\GroupRepository;
use Amasty\Mostviewed\Model\ResourceModel\Group\TogetherCondition\BoughtTogetherIndex;
use Amasty\Mostviewed\Model\ResourceModel\Group\TogetherCondition\ViewedTogetherIndex;
use Amasty\Mostviewed\Model\ResourceModel\Product\Collection;
use Amasty\Mostviewed\Model\ResourceModel\Product\CollectionFactory;
use Amasty\Mostviewed\Model\ResourceModel\Product\LoadBoughtTogether;
use Amasty\Mostviewed\Model\ResourceModel\Product\LoadViews;
use Amasty\Mostviewed\Model\ResourceModel\RuleIndex;
use Amasty\Mostviewed\Test\Unit\Traits;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Visibility;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ProductProviderTest
 *
 * @see ProductProvider
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ProductProviderTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    private $sortingMethods = [
        Sortby::BESTSELLERS,
        Sortby::TOP_RATED,
        Sortby::REVIEWS_COUNT,
        Sortby::MOST_VIEWED
    ];

    /**
     * @var ProductProvider
     */
    private $model;

    /**
     * @var Product|MockObject
     */
    private $product;

    /**
     * @var Wrapper
     */
    private $sortingMethodsProvider;

    protected function setup(): void
    {
        $this->product = $this->createMock(Product::class);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $indexResource = $this->createMock(RuleIndex::class);
        $productCollectionFactory = $this->createMock(CollectionFactory::class);
        $groupRepository = $this->createMock(GroupRepository::class);
        $catalogProductVisibility = $this->createMock(Visibility::class);
        $catalogConfig = $this->createMock(Config::class);
        $stockHelper = $this->createMock(Stock::class);
        $config = $this->createMock(MostviewedHelperConfig::class);
        $loadViews = $this->createMock(LoadViews::class);
        $loadBoughtTogether = $this->createMock(LoadBoughtTogether::class);
        $viewedTogetherIndex = $this->createMock(ViewedTogetherIndex::class);
        $boughtTogetherIndex = $this->createMock(BoughtTogetherIndex::class);
        $this->sortingMethodsProvider = $this->getMockBuilder(Wrapper::class)
            ->disableOriginalConstructor()
            ->setMethods(['isAvailable', 'getMethodByCode'])
            ->getMock();

        $type = $this->getMockBuilder(\Magento\Catalog\Model\Product\Type\AbstractType::class)
            ->setMethods(['getAssociatedProductIds', 'getUsedProductIds', 'getOptionsIds', 'getSelectionsCollection'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $type->expects($this->any())->method('getAssociatedProductIds')->willReturn([1]);
        $type->expects($this->any())->method('getUsedProductIds')->willReturn([2]);
        $type->expects($this->any())->method('getOptionsIds')->willReturn([5]);
        $type->expects($this->any())->method('getSelectionsCollection')->willReturn([]);
        $this->product->expects($this->any())->method('getTypeInstance')->willReturn($type);
        $this->product->expects($this->any())->method('getId')->willReturn(10);

        $this->model = new ProductProvider(
            $storeManager,
            $indexResource,
            $productCollectionFactory,
            $groupRepository,
            $catalogProductVisibility,
            $catalogConfig,
            $stockHelper,
            $config,
            $this->sortingMethodsProvider,
            $loadViews,
            $loadBoughtTogether,
            $boughtTogetherIndex,
            $viewedTogetherIndex
        );
    }

    /**
     * @covers ProductProvider::applySorting
     * @dataProvider applySortingDataProvider
     */
    public function testApplySorting(string $sorting, bool $isSortingEnabled): void
    {
        $wrapper = $this->createMock(Wrapper::class);
        $collection = $this->createMock(Collection::class);
        $select = $this->createMock(Select::class);

        if ($sorting === 'test' || (!$isSortingEnabled && in_array($sorting, $this->sortingMethods))) {
            $matcherSelect = $this->once();
            $matcherOrder = $this->once();
        } else {
            $matcherSelect = $this->never();
            $matcherOrder = $this->exactly(2);
        }

        $this->sortingMethodsProvider->expects($this->any())
            ->method('isAvailable')
            ->willReturn($isSortingEnabled);

        $this->sortingMethodsProvider->expects($this->any())
            ->method('getMethodByCode')
            ->willReturn($wrapper);

        $collection->expects($matcherSelect)->method('getSelect')->willReturn($select);
        $collection->expects($matcherOrder)->method('setOrder')->willReturn($collection);

        $this->invokeMethod($this->model, 'applySorting', [$sorting, $collection]);
    }

    /**
     * Data provider for applySorting test
     * @return array
     */
    public function applySortingDataProvider()
    {
        return [
            [Sortby::NAME, false],
            [Sortby::PRICE_ASC, false],
            [Sortby::PRICE_DESC, false],
            [Sortby::NEWEST, true],
            [Sortby::BESTSELLERS, true],
            [Sortby::MOST_VIEWED, true],
            [Sortby::REVIEWS_COUNT, true],
            [Sortby::TOP_RATED, true],
            [Sortby::BESTSELLERS, false],
            ['test', false],
        ];
    }
}
