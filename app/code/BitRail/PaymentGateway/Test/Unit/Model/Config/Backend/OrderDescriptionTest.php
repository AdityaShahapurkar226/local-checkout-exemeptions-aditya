<?php

namespace BitRail\PaymentGateway\Test\Unit\Model\Config\Backend;

use BitRail\PaymentGateway\Model\Config\Backend\OrderDescription;

use Magento\Framework\App\Config\ScopeConfigInterface;

use PHPUnit\Framework\TestCase;

class OrderDescriptionTest extends TestCase
{
    /**
     * @var OrderDescription
     */
    private $orderDescription;

    protected function setUp(): void
    {
        $scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderDescription = new OrderDescription($scopeConfig);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->orderDescription = null;

        parent::tearDown();
    }

    public function testProcessValueEmpty()
    {
        $this->assertEquals($this->orderDescription->processValue(null), 'Purchase in e-shop');
    }

    public function testProcessValue()
    {
        $this->assertEquals($this->orderDescription->processValue('test'), 'test');
    }
}
