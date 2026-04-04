<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

/**
 * @codingStandardsIgnoreFile
 */

namespace Amasty\Mostviewed\Test\Unit\Block\Product;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Amasty\Mostviewed\Block\Product\BundlePack;
use Amasty\Mostviewed\Model\Customer\GroupValidator;
use Amasty\Mostviewed\Model\OptionSource\DiscountType;
use Amasty\Mostviewed\Model\Pack;
use Amasty\Mostviewed\Test\Unit\Traits;
use Magento\Framework\DataObject;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class BundlePackTest
 *
 * @see BundlePack
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BundlePackTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var BundlePack|MockObject
     */
    private $block;

    /**
     * @var \Amasty\Mostviewed\Api\PackRepositoryInterface|MockObject
     */
    private $packRepository;

    protected function setup(): void
    {
        $storeManager = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->packRepository = $this->createMock(\Amasty\Mostviewed\Api\PackRepositoryInterface::class);
        $store = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $priceCurrency = $this->createMock(PriceCurrencyInterface::class);
        $groupValidator = $this->createMock(GroupValidator::class);
        $groupValidator->expects($this->any())->method('validate')->willReturn(true);

        $storeManager->expects($this->any())->method('getStore')->willReturn($store);
        $store->expects($this->any())->method('getId')->willReturn(1);
        $priceCurrency->expects($this->any())->method('format')->willReturnArgument(0);
        $priceCurrency->expects($this->any())->method('round')->willReturnArgument(0);

        $this->block = $this->getObjectManager()->getObject(
            BundlePack::class,
            [
                '_storeManager' => $storeManager,
                'packRepository' => $this->packRepository,
                'groupValidator' => $groupValidator,
                'priceCurrency' => $priceCurrency,
            ]
        );
    }

    /**
     * @covers BundlePack::toHtml
     */
    public function testToHtml()
    {
        $this->block = $this->createPartialMock(
            BundlePack::class,
            ['isBundlePacksExists', 'getParentHtml', 'getProduct']
        );
        $config = $this->createMock(\Amasty\Mostviewed\Helper\Config::class);
        $config->expects($this->any())->method('getBlockPosition')->willReturn('es');

        $this->setProperty($this->block, 'config', $config);
        $this->block->expects($this->any())->method('isBundlePacksExists')->willReturn(true);
        $this->block->expects($this->any())->method('getParentHtml')->willReturn('test');

        $this->setProperty($this->block, '_nameInLayout', 'false');
        $this->assertEquals('', $this->block->toHtml());
        $this->setProperty($this->block, '_nameInLayout', 'test');
        $this->assertEquals('test', $this->block->toHtml());
    }

    /**
     * @covers BundlePack::isBundlePacksExists
     */
    public function testIsBundlePacksExists()
    {
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);

        $pack = $this->createMock(Pack::class);
        $pack->expects($this->any())->method('getCustomerGroupIds')->willReturn(1);

        $product->expects($this->any())->method('isSaleable')->willReturnOnConsecutiveCalls(false, true, true);
        $this->packRepository->expects($this->any())->method('getPacksByParentProductsAndStore')
            ->willReturnOnConsecutiveCalls(false, [$pack]);

        $this->block->setProduct($product);

        $this->assertFalse($this->block->isBundlePacksExists());
        $this->assertFalse($this->block->isBundlePacksExists());
        $this->assertTrue($this->block->isBundlePacksExists());
    }

    /**
     * @covers BundlePack::getProductDiscount
     * @dataProvider getProductDiscountDataProvider
     */
    public function testGetProductDiscount($config, $productId, $isParent, $result)
    {
        $this->assertEquals($result, $this->block->getProductDiscount($config, $productId, $isParent));
    }

    /**
     * Data provider for getProductIdsByType test
     * @return array
     */
    public function getProductDiscountDataProvider()
    {
        return [
            [
                [
                    'discount_type' => DiscountType::PERCENTAGE,
                    'apply_for_parent' => true,
                    'parent_info' => [
                        'discount_amount' => 5
                    ],
                    'discount_amount' => 2
                ],
                1,
                true,
                '5%'
            ],
            [
                [
                    'discount_type' => DiscountType::PERCENTAGE,
                    'apply_for_parent' => false,
                    'parent_info' => [
                        'discount_amount' => 5
                    ],
                    'discount_amount' => 2
                ],
                1,
                true,
                ''
            ],
            [
                [
                    'discount_type' => DiscountType::PERCENTAGE,
                    'apply_for_parent' => true,
                    'parent_info' => [
                        'discount_amount' => 5
                    ],
                    'products' => [
                        1 => [
                            'discount_amount' => 10
                        ]
                    ],
                    'discount_amount' => 2
                ],
                1,
                false,
                '10%'
            ],
            [
                [
                    'discount_type' => DiscountType::FIXED,
                    'apply_for_parent' => true,
                    'parent_info' => [
                        'discount_amount' => 5
                    ],
                    'products' => [
                        1 => [
                            'discount_amount' => null
                        ]
                    ],
                    'discount_amount' => 2
                ],
                1,
                false,
                '-2'
            ],
            [
                [
                    'discount_type' => DiscountType::CONDITIONAL,
                    'apply_for_parent' => true,
                    'parent_info' => [
                        'discount_amount' => 5
                    ],
                    'products' => [
                        1 => [
                            'discount_amount' => 90
                        ],
                        2 => [
                            'discount_amount' => null
                        ],
                        3 => [
                            'discount_amount' => null
                        ]
                    ],
                    'discount_amount' => 2,
                    'conditional_discounts' => [
                        2 => 10,
                        3 => 15,
                        5 => 20
                    ]
                ],
                1,
                false,
                '15%'
            ],
            [
                [
                    'discount_type' => DiscountType::CONDITIONAL,
                    'apply_for_parent' => true,
                    'parent_info' => [
                        'discount_amount' => 5
                    ],
                    'products' => [
                        1 => [
                            'discount_amount' => 90
                        ],
                        2 => [
                            'discount_amount' => null
                        ],
                        3 => [
                            'discount_amount' => null
                        ],
                        4 => [
                            'discount_amount' => null
                        ],
                    ],
                    'discount_amount' => 2,
                    'conditional_discounts' => [
                        2 => 10,
                        3 => 15,
                        5 => 20
                    ]
                ],
                1,
                false,
                '20%'
            ]
        ];
    }

    /**
     * @covers BundlePack::getDiscountResult
     * @dataProvider getDiscountResultDataProvider
     */
    public function testGetDiscountResult($data, $result)
    {
        $this->assertEquals($result, $this->block->getDiscountResult($data));
    }

    /**
     * Data provider for getProductIdsByType test
     * @return array
     */
    public function getDiscountResultDataProvider()
    {
        return [
            [
                [
                    'parent_info' => ['price' => 10, 'qty' => 1],
                    'products' => [['price' => 1, 'qty' => 1], ['price' => 2, 'qty' => 1], ['price' => 3, 'qty' => 1]],
                    'discount_type' => DiscountType::FIXED,
                    'discount_amount' => 5,
                    'apply_for_parent' => true,
                ],
                [
                    'final_price' => 5,
                    'discount' => 11,
                ]
            ],
            [
                [
                    'parent_info' => ['price' => 10, 'qty' => 1],
                    'products' => [['price' => 4, 'qty' => 1], ['price' => 2, 'qty' => 1], ['price' => 3, 'qty' => 1]],
                    'discount_type' => DiscountType::FIXED,
                    'discount_amount' => 5,
                    'apply_for_parent' => false,
                ],
                [
                    'final_price' => 10,
                    'discount' => 9
                ]
            ],
            [
                [
                    'parent_info' => ['price' => 10, 'qty' => 1],
                    'products' => [],
                    'discount_type' => DiscountType::FIXED,
                    'discount_amount' => 5,
                    'apply_for_parent' => false,
                ],
                [
                    'final_price' => 10,
                    'discount' => 0,
                ]
            ],
            [
                [
                    'parent_info' => ['price' => 10, 'qty' => 1],
                    'products' => [['price' => 4, 'qty' => 2], ['price' => 2, 'qty' => 1], ['price' => 3, 'qty' => 1]],
                    'discount_type' => DiscountType::FIXED,
                    'discount_amount' => 5,
                    'apply_for_parent' => false,
                ],
                [
                    'final_price' => 10,
                    'discount' => 13
                ]
            ],
        ];
    }

    /**
     * @covers BundlePack::applyDiscount
     * @dataProvider applyDiscountDataProvider
     */
    public function testApplyDiscount($priceInfo, $config, $result)
    {
        $this->assertEquals($result, $this->invokeMethod(
            $this->block,
            'applyDiscount',
            [$priceInfo, $config]
        ));
    }

    /**
     * Data provider for applyDiscount test
     * @return array
     */
    public function applyDiscountDataProvider()
    {
        return [
            [
                ['price' => 10, 'qty' => 1],
                [
                    'discount_type' => DiscountType::FIXED,
                    'discount_amount' => 5,
                ],
                5
            ],
            [
                ['price' => 5, 'qty' => 1],
                [
                    'discount_type' => DiscountType::FIXED,
                    'discount_amount' => 10,
                ],
                0
            ],
            [
                ['price' => 20, 'qty' => 1],
                [
                    'discount_type' => DiscountType::PERCENTAGE,
                    'discount_amount' => 10,
                ],
                18
            ],
            [
                ['price' => 20, 'qty' => 11],
                [
                    'discount_type' => DiscountType::PERCENTAGE,
                    'discount_amount' => 10,
                ],
                198
            ],
            [
                ['price' => 20, 'qty' => 2],
                [
                    'discount_type' => DiscountType::FIXED,
                    'discount_amount' => 10,
                ],
                20
            ],
        ];
    }
}
