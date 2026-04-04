<?php

namespace BitRail\PaymentGateway\Test\Stubs;

use BitRail\PaymentGateway\Gateway\Http\Client\BitrailClient;

class BitrailClientStub extends BitrailClient
{
    /**
     * {@inheritdoc}
     */
    public function getCredentials(): array
    {
        return parent::getCredentials();
    }

    /**
     * {@inheritdoc}
     */
    public function checkResponse($response, ?callable $callback = null): array
    {
        return parent::checkResponse($response, $callback);
    }
}
