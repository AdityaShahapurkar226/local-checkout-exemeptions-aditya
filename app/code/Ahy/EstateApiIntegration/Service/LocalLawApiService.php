<?php

namespace Ahy\EstateApiIntegration\Service;

use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;

class LocalLawApiService
{
    private const BASE_URL = 'https://local-laws-dashboard-production.up.railway.app';

    private Curl $curl;
    private LoggerInterface $logger;
    private string $token;

    public function __construct(
        Curl $curl,
        LoggerInterface $logger
    ) {
        $this->curl = $curl;
        $this->logger = $logger;

        // Replace with dynamic token later
        $this->token = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJrb21hbCIsInJvbGUiOiJhZG1pbiIsImV4cCI6MTc3NTA1MjgzOX0._2hajVPS7teCxbA4B64j4KVVjpTBzviB9a6npwlVG-8';
    }

    /**
     *  Generic API call
     */
    public function validateRule($ruleType, $state, $productType, $city = null)
    {
        try {
            $params = [
                'rule_type'    => $ruleType,
                'state'        => strtoupper(trim($state)),
                'product_type' => $productType
            ];

            if (!empty($city)) {
                $params['city'] = $city;
            }

            $url = self::BASE_URL . '/rules/validate?' . http_build_query($params);

            //  Headers
            $this->curl->addHeader("Authorization", "Bearer " . $this->token);
            $this->curl->addHeader("Content-Type", "application/json");

            //  Timeout (important for Railway)
            $this->curl->setOption(CURLOPT_TIMEOUT, 10);

            $this->logger->info('[LocalLawApi] Request', [
                'url' => $url
            ]);

            // Call API
            $this->curl->get($url);

            $status = $this->curl->getStatus();
            $response = $this->curl->getBody();

            $this->logger->info('[LocalLawApi] Response', [
                'status' => $status,
                'body'   => $response
            ]);

            if ($status !== 200) {
                throw new \Exception("API Error: HTTP {$status} - {$response}");
            }

            $decoded = json_decode($response, true);

            if (!$decoded) {
                throw new \Exception("Invalid JSON response");
            }

            return $decoded;

        } catch (\Exception $e) {
            $this->logger->error('[LocalLawApi ERROR] ' . $e->getMessage());
            return false;
        }
    }

    /**
     *  Regulated Weapon
     */
    public function validateRegulatedWeapon($state, $productType, $city = null)
    {
        return $this->validateRule('regulated_weapon', $state, $productType, $city);
    }

    /**
     * Magazine
     */
    public function validateMagazine($state, $productType, $city = null)
    {
        return $this->validateRule('magazine', $state, $productType, $city);
    }
    public function getAvailableCities($ruleType, $state)
{
    try {
        $params = [
            'rule_type' => $ruleType,
            'state'     => strtoupper(trim($state))
        ];

        $url = self::BASE_URL . '/rules/available-cities?' . http_build_query($params);

        // Headers
        $this->curl->addHeader("Authorization", "Bearer " . $this->token);
        $this->curl->addHeader("Content-Type", "application/json");

        $this->curl->setOption(CURLOPT_TIMEOUT, 10);

        $this->logger->info('[LocalLawApi] Available Cities Request', [
            'url' => $url
        ]);

        $this->curl->get($url);

        $status = $this->curl->getStatus();
        $response = $this->curl->getBody();

        $this->logger->info('[LocalLawApi] Available Cities Response', [
            'status' => $status,
            'body'   => $response
        ]);

        if ($status !== 200) {
            throw new \Exception("API Error: HTTP {$status} - {$response}");
        }

        $decoded = json_decode($response, true);

        if (!is_array($decoded)) {
            throw new \Exception("Invalid cities response");
        }

        return $decoded;

    } catch (\Exception $e) {
        $this->logger->error('[LocalLawApi ERROR - Cities] ' . $e->getMessage());
        return [];
    }
}
}