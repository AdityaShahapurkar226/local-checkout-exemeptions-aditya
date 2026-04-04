<?php
namespace Ahy\EfflApiIntegration\Service;

use Magento\Framework\HTTP\Client\Curl;
use Ahy\EfflApiIntegration\Logger\Logger as EfflLogger;

class CensusGeocodingService
{
    protected $curl;
    protected $logger;

    /** @var string */
    private $fallbackApiUrl = 'https://api.opencagedata.com/geocode/v1/json';

    /** @var string */
    private $fallbackApiKey = '454e084621304a9184bc485ec2bd8e66';

    public function __construct(
        Curl $curl,
        EfflLogger $logger
    ) {
        $this->curl = $curl;
        $this->logger = $logger;
    }

    /**
     * Try Census API first, fallback to OpenCage if needed.
     *
     * @param array $payload
     * @return array
     */
    public function geocodeAddress(array $payload): array
    {
        $this->logger->info('[EfflApiIntegration] Starting geocode request.');

        // 1) Attempt Census API
        $censusResponse = $this->callCensusApi($payload);

        if ($this->isValidCensusResponse($censusResponse)) {
            $this->logger->info('[EfflApiIntegration] Using Census API result.');
            return $censusResponse;
        }

        $this->logger->warning('[EfflApiIntegration] Census API failed or returned no results. Triggering fallback.');

        // 2) Attempt Fallback (OpenCage)
        $fallbackResponse = $this->callFallbackApi($payload);

        if ($this->isValidFallbackResponse($fallbackResponse)) {
            $this->logger->info('[EfflApiIntegration] Using OpenCage fallback result.');
            return $this->transformFallbackResponse($fallbackResponse);
        }

        $this->logger->error('[EfflApiIntegration] Fallback API also failed. Returning empty response.');
        return ['error' => 'Both Census and fallback geocoding failed'];
    }

    /**
     * Call Census Geocoding API.
     */
    private function callCensusApi(array $payload): array
    {
        $url = "https://geocoding.geo.census.gov/geocoder/locations/address";

        $params = [
            'street'    => $payload['street'] ?? '',
            'city'      => $payload['city'] ?? '',
            'state'     => $payload['state'] ?? '',
            'zip'       => $payload['zip'] ?? '',
            'benchmark' => $payload['benchmark'] ?? 'Public_AR_Current',
            'format'    => 'json'
        ];

        try {
            $query = http_build_query($params);
            $finalUrl = $url . '?' . $query;

            $this->logger->info('[EfflApiIntegration] Census Geocoding URL: ' . $finalUrl);

            $this->curl->get($finalUrl);
            $response = $this->curl->getBody();

            $this->logger->info('[EfflApiIntegration] Census Geocoding Response: ' . $response);

            return json_decode($response, true) ?: [];
        } catch (\Exception $e) {
            $this->logger->error('[EfflApiIntegration] Census Geocoding Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Call OpenCage fallback API.
     */
    private function callFallbackApi(array $payload): array
    {
        $formattedAddress = urlencode(
            trim(($payload['street'] ?? '') . ', ' . ($payload['city'] ?? '') . ', ' . ($payload['state'] ?? '') . ' ' . ($payload['zip'] ?? ''))
        );

        $url = "{$this->fallbackApiUrl}?q={$formattedAddress}&key={$this->fallbackApiKey}&pretty=1&no_annotations=1&limit=1";

        try {
            $this->logger->info('[EfflApiIntegration] Fallback (OpenCage) Geocoding URL: ' . $url);

            $this->curl->get($url);
            $response = $this->curl->getBody();

            return json_decode($response, true) ?: [];
        } catch (\Exception $e) {
            $this->logger->error('[EfflApiIntegration] OpenCage Geocoding Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if Census response has valid coordinates.
     */
    private function isValidCensusResponse(array $response): bool
    {
        return !empty($response['result']['addressMatches'][0]['coordinates']['x'])
            && !empty($response['result']['addressMatches'][0]['coordinates']['y']);
    }

    /**
     * Check if fallback response has valid coordinates.
     */
    private function isValidFallbackResponse(array $response): bool
    {
        return !empty($response['results'][0]['geometry']['lat'])
            && !empty($response['results'][0]['geometry']['lng']);
    }

    /**
     * Transform fallback response into Census-like structure.
     */
    private function transformFallbackResponse(array $response): array
    {
        $lat = $response['results'][0]['geometry']['lat'];
        $lng = $response['results'][0]['geometry']['lng'];

        return [
            'result' => [
                'addressMatches' => [
                    [
                        'coordinates' => [
                            'x' => $lng, // longitude
                            'y' => $lat  // latitude
                        ]
                    ]
                ]
            ]
        ];
    }
}
