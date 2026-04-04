<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace BitRail\PaymentGateway\Test\Unit\Model\Ui;

use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Model\Config\Source\Allmethods;
use Magento\Framework\View\Asset\Repository;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;

use BitRail\PaymentGateway\Gateway\Http\Client\ClientMock;
use BitRail\PaymentGateway\Model\Ui\ConfigProvider;

use PHPUnit\Framework\TestCase;

class ConfigProviderTest extends TestCase
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    protected function setUp(): void
    {
        $this->markTestSkipped('Not sure how to mock ObjectManager');

        $data = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->getMock();
        $escaper = $this->getMockBuilder(Escaper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $allMethods = $this->getMockBuilder(AllMethods::class)
            ->disableOriginalConstructor()
            ->getMock();
        $urlInterface = $this->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $assetRepository = $this->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configProvider = new ConfigProvider(
            $data, $escaper, $allMethods, $urlInterface, $scopeConfig, $assetRepository, $customerSession
        );

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->configProvider = null;

        parent::tearDown();
    }

    public function testGetConfig()
    {
        $objectManager = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectManager->expects($this->once())
            ->method('getInstance')
            ->willReturn(new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this));

        $this->assertEquals(
            [
                'payment' => [
                    ConfigProvider::CODE => [
                        'apiUrl' => 'https://api.bitrail.io/v1/',
                        'nonceCode' => ClientMock::getNonceCode(),
                    ]
                ]
            ],
            $this->configProvider->getConfig()
        );
    }
}
