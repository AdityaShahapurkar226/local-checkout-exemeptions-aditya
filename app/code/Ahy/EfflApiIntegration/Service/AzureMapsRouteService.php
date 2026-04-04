<?php

namespace Ahy\EfflApiIntegration\Service;

use GuzzleHttp\Client;
use Magento\Framework\App\DeploymentConfig;

class AzureMapsRouteService
{
    private Client $client;
    private ?string $subscriptionKey;
    private ?string $routeUrl;

    public function __construct(
        Client $client,
        DeploymentConfig $deploymentConfig
    ) {
        $this->client = $client;

        $azureConfig = $deploymentConfig->get('azure_maps') ?? [];

        $this->subscriptionKey = $azureConfig['subscription_key'] ?? null;
        $this->routeUrl        = $azureConfig['url'] ?? null;
    }

    public function getDrivingDistanceMiles(
        float $originLat,
        float $originLng,
        float $destLat,
        float $destLng
    ): ?float {
        if (!$this->subscriptionKey || !$this->routeUrl) {
            return null;
        }

        $query = "{$originLat},{$originLng}:{$destLat},{$destLng}";

        try {
            $response = $this->client->get($this->routeUrl, [
                'query' => [
                    'api-version'       => '1.0',
                    'subscription-key' => $this->subscriptionKey,
                    'query'             => $query,
                    'travelMode'        => 'car'
                ],
                'timeout' => 5
            ]);
        } catch (\Throwable $e) {
            return null; // never break checkout
        }

        $data = json_decode($response->getBody()->getContents(), true);

        if (!empty($data['routes'][0]['summary']['lengthInMeters'])) {
            return round(
                $data['routes'][0]['summary']['lengthInMeters'] / 1609.344,
                2
            );
        }

        return null;
    }
}
