<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace BitRail\PaymentGateway\Test\Unit\Model\Adminhtml\Source;

use Magento\Framework\App\State;

use BitRail\PaymentGateway\Model\Adminhtml\Source\PaymentEnvironment;

use PHPUnit\Framework\TestCase;

class PaymentEnvironmentTest extends TestCase
{
    /**
     * @var PaymentEnvironment
     */
    private $paymentEnvironment;

    protected function setUp(): void
    {
        $state = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->paymentEnvironment = new PaymentEnvironment($state);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        $this->paymentEnvironment = null;

        parent::tearDown();
    }

    public function testToOptionArray()
    {
        $this->assertEquals(
            [
                [
                    'value' => 'prod',
                    'label' => 'Production',
                ],
                [
                    'value' => 'sandbox',
                    'label' => 'Sandbox',
                ],
            ],
            $this->paymentEnvironment->toOptionArray()
        );
    }
}
