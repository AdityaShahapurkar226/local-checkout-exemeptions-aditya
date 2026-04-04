<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Mostviewed\Test\Unit\Block\Checkout\Cart;

use Amasty\Mostviewed\Block\Checkout\Cart\Messages;
use Amasty\Mostviewed\Model\Pack;
use Amasty\Mostviewed\Test\Unit\Traits;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class MessagesTest
 *
 * @see Messages
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MessagesTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var Messages
     */
    private $block;

    protected function setup(): void
    {
        $dataObjectFactory = $this->createMock(\Magento\Framework\DataObjectFactory::class);
        $priceCurrency = $this->createMock(PriceCurrencyInterface::class);

        $dataObjectFactory->expects($this->any())->method('create')->willReturnArgument(0);
        $priceCurrency->expects($this->any())->method('format')->willReturnArgument(0);

        $this->block = $this->getObjectManager()->getObject(
            Messages::class,
            [
                'dataObjectFactory' => $dataObjectFactory,
                'priceCurrency' => $priceCurrency,
            ]
        );
    }

    /**
     * @covers Messages::validatePacks
     */
    public function testValidatePacks()
    {
        $result = [
            [
                'data' => [
                    'products' => ['2'],
                    'pack_id' => 2,
                    'discount' => null,
                    'message' => 'test',
                    'full_discount' => 0
                ]
            ]
        ];
        $pack1 = $this->createMock(Pack::class);
        $pack2 = $this->createMock(Pack::class);

        $pack1->expects($this->any())->method('getProductIds')->willReturn('1');
        $pack2->expects($this->any())->method('getProductIds')->willReturn('2');
        $pack2->expects($this->any())->method('getPackId')->willReturn(2);
        $pack2->expects($this->any())->method('getCartMessage')->willReturn('test');

        $this->assertEquals([], $this->invokeMethod($this->block, 'validatePacks', [[], [], true]));

        $this->setProperty($this->block, 'productsInCart', [1], Messages::class);
        $this->assertEquals($result, $this->invokeMethod($this->block, 'validatePacks', [[], [$pack1, $pack2], true]));
    }

    /**
     * @covers Messages::convertMessage
     * @dataProvider convertMessageDataProvider
     */
    public function testConvertMessage($messageText, $result)
    {
        $this->block = $this->createPartialMock(Messages::class, ['generateNamesContent', 'escapeHtml']);
        $message = $this->getMockBuilder(\Amasty\Mostviewed\Block\Checkout\Cart\Messages::class)
            ->setMethods(['getDiscount', 'escapeHtml', 'getMessage'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->block->expects($this->any())->method('escapeHtml')->willReturn($messageText);
        $this->block->expects($this->any())->method('generateNamesContent')->willReturn('test');
        $message->expects($this->any())->method('getDiscount')->willReturn(10);

        $this->assertEquals($result, $this->block->convertMessage($message));
    }

    /**
     * Data provider for convertMessage test
     * @return array
     */
    public function convertMessageDataProvider()
    {
        return [
            ['', ''],
            ['@{product_names}@', '@test@'],
            ['@{discount_amount}@', '@10@'],
        ];
    }

    /**
     * @covers Messages::generateDiscount
     */
    public function testGenerateDiscount()
    {
        $pack = $this->createMock(Pack::class);

        $pack->expects($this->any())->method('getDiscountAmount')->willReturn(10);
        $pack->expects($this->any())->method('getDiscountType')->willReturnOnConsecutiveCalls(1, 0);

        $this->assertEquals('10%', $this->invokeMethod($this->block, 'generateDiscount', [$pack]));
        $this->assertEquals(10, $this->invokeMethod($this->block, 'generateDiscount', [$pack]));
    }
}
