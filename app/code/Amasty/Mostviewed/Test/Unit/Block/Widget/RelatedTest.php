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

use Amasty\Mostviewed\Block\Widget\Related;
use Amasty\Mostviewed\Helper\Quote;
use Amasty\Mostviewed\Model\OptionSource\BlockPosition;
use Amasty\Mostviewed\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class RelatedTest
 *
 * @see Related
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RelatedTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Related
     */
    private $block;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $coreRegistry;

    /**
     * @var Quote
     */
    private $quoteHelper;

    protected function setup(): void
    {
        $this->request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->setMethods(['getFullActionName'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->coreRegistry = $this->createMock(\Magento\Framework\Registry::class);
        $this->quoteHelper = $this->createMock(Quote::class);

        $this->block = $this->getObjectManager()->getObject(
            Related::class,
            [
                '_request' => $this->request,
                '_coreRegistry' => $this->coreRegistry,
                'quoteHelper' => $this->quoteHelper,
            ]
        );
    }

    /**
     * @covers PackList::getEntity
     * @dataProvider getEntityDataProvider
     */
    public function testGetEntity($action, $result)
    {
        $this->request->expects($this->any())->method('getFullActionName')->willReturn($action);
        $this->coreRegistry->expects($this->any())->method('registry')->willReturn(2);
        $this->quoteHelper->expects($this->any())->method('getLastAddedProductInCart')->willReturn(1);

        $this->assertEquals($result, $this->invokeMethod($this->block, 'getEntity'));
    }

    /**
     * Data provider for getEntity test
     * @return array
     */
    public function getEntityDataProvider()
    {
        return [
            ['catalog_product_view', 2],
            ['catalog_category_view', 2],
            ['checkout_cart_index', 1],
            ['test', null],
        ];
    }

    /**
     * @covers PackList::getCssClass
     */
    public function testGetCssClass()
    {
        $this->block->setPosition(BlockPosition::CART_BEFORE_CROSSSEL);
        $this->assertEquals('crosssell', $this->block->getCssClass());
        $this->block->setPosition(BlockPosition::CART_AFTER_CROSSSEL);
        $this->assertEquals('crosssell', $this->block->getCssClass());
        $this->block->setPosition('test');
        $this->assertEquals('widget', $this->block->getCssClass());
    }
}
