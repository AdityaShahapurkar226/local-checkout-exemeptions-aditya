<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace BitRail\PaymentGateway\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;

use BitRail\PaymentGateway\Observer\DataAssignObserver;

use PHPUnit\Framework\TestCase;

class DataAssignObserverTest extends TestCase
{
    /**
     * @var Observer
     */
    private $observerContainer;
    /**
     * @var DataAssignObserver
     */
    private $observer;

    protected function setUp(): void
    {
        $this->observerContainer = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->getMock();
        $checkoutSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMethodFacade = $this->createMock(MethodInterface::class);
        $dataObject = new DataObject(
            [
                'method' => 'bitrail_gateway',
                'orderVerificatioToken' => 'some_token',
            ]
        );

        $this->observerContainer->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($event);
        $event->expects($this->once())
            ->method('getDataByKey')
            ->willReturnMap(
                [
                    [AbstractDataAssignObserver::METHOD_CODE, $paymentMethodFacade],
                    [AbstractDataAssignObserver::DATA_CODE, $dataObject]
                ]
            );

        $scopeConfig->expects($this->once())
            ->method('getValue')
            ->willReturn('prod');

        $this->observer = new DataAssignObserver($checkoutSession, $scopeConfig);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->observer = null;
        $this->observerContainer = null;

        parent::tearDown();
    }

    public function testExectute()
    {
        $this->expectException(\Exception::class);
        $this->observer->execute($this->observerContainer);
    }
}
