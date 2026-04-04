<?php

namespace BitRail\PaymentGateway\Test\Feature;

use BitRail\PaymentGateway\Gateway\Http\Client\BitrailOrderTokenizer;

use PHPUnit\Framework\TestCase;

class BitrailOrderTokenizerTest extends TestCase
{
    public function testTokenIsValid()
    {
        $quoteId = 1;
        $token = BitrailOrderTokenizer::getOrderToken($quoteId);

        $this->assertTrue(BitrailOrderTokenizer::tokenIsValid($quoteId, $token));
        $this->assertFalse(BitrailOrderTokenizer::tokenIsValid($quoteId, 'someToken'));
    }
}
