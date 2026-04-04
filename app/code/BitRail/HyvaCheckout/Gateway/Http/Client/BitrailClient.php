<?php

namespace Bitrail\HyvaCheckout\Gateway\Http\Client;

use Bitrail\HyvaCheckout\Model\Config\ConfigProvider;
use Magento\Framework\HTTP\Client\Curl;

class BitrailClient
{
    private $httpClient;
    private $configProvider;

    public function __construct(Curl $httpClient, ConfigProvider $configProvider)
    {
        $this->httpClient = $httpClient;
        $this->configProvider = $configProvider;
    }

    public function getApiUrl(): string
    {
        $environment = $this->configProvider->getEnvironment();
        if (!in_array($environment, ['prod', 'sandbox', 'qa'], true)) {
            throw new \InvalidArgumentException('Invalid environment for BitRail Gateway');
        }

        return match ($environment) {
            'prod' => 'https://api.bitrail.io/v1/',
            'sandbox' => 'https://api.sandbox.bitrail.io/v1/',
            default => 'https://api.qa.bitrail.io/v1/',
        };
    }

    /**
     * Token request to BitRail platform as service application.
     */
    public function getApiAuthToken(): string
    {
        $credentials = [
            'client_id' => $this->configProvider->getClientId(),
            'client_secret' => $this->configProvider->getClientSecret(),
        ];
        $this->httpClient->post($this->getApiUrl() . '/auth/token', [
            'scope' => 'everything',
            'grant_type' => 'client_credentials',
            'client_id' => $credentials['client_id'],
            'client_secret' => $credentials['client_secret'],
            'redirect_uri' => 'none',
        ]);

        $response = $this->httpClient->getBody();
        $responseData = json_decode($response, true);

        if (!$responseData['success'] || !isset($responseData['data'][0]['access_token'])) {
            throw new \RuntimeException('Failed to retrieve access token.');
        }

        return $responseData['data'][0]['access_token'];
    }

    /**
     * Verify transaction with verification_token provided by the BitRail order form.
     */
    public function verifyTransaction(string $token): string
    {
        $url = $this->getApiUrl() . 'transactions/verify?token=' . urlencode($token);
        $this->httpClient->addHeader('Authorization', 'Bearer ' . $this->getApiAuthToken());
        $this->httpClient->get($url);

        $response = $this->httpClient->getBody();
        $responseData = json_decode($response, true);

        if (!$responseData['success'] || !isset($responseData['data'][0]['order_token'])) {
            $errorMessage = $responseData['errors'][0]['message'] ?? 'Failed to verify transaction.';
            throw new \RuntimeException($errorMessage);
        }

        return $responseData['data'][0]['order_token'];
    }
}
