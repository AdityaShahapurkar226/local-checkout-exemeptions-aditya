<?php

namespace Ahy\EstateApiIntegration\Service;

use Magento\Framework\HTTP\Client\Curl;
use Ahy\EstateApiIntegration\Logger\Logger;
use Magento\Framework\App\DeploymentConfig;

/**
 * Service class to interact with the Orchid Estate API
 *
 * Provides functionality to validate product UPC codes against a given zip code
 * by calling an external API configured in Magento's deployment configuration.
 */
class EstateApiService
{
    /**
     * @var Curl
     */
    private Curl $curl;

    /**
     * @var LoggerI
     */
    private Logger $logger;

    /**
     * @var DeploymentConfig
     */
    private DeploymentConfig $deploymentConfig;

    /**
     * EstateApiService constructor.
     *
     * @param Curl $curl
     * @param Logger $logger
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        Curl $curl,
        Logger $logger,
        DeploymentConfig $deploymentConfig
    ) {
        $this->curl = $curl;
        $this->logger = $logger;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Validates the given product UPC code against the provided zip code via Orchid API
     *
     * @param string $upc The UPC code of the product to validate
     * @param string $zip The zip code for validation
     * @return mixed Returns:
     *               - integer for old numeric responses
     *               - wrapped associative array for new format
     *               - 4 for errors
     */
    public function validateProductUpcWithZip(string $upc, string $zip)
    {
        $apiKey  = $this->deploymentConfig->get('orchid_api/key');
        $apiUrl  = $this->deploymentConfig->get('orchid_api/url');

        if (!$apiKey || !$apiUrl) {
            $this->logger->error("Orchid API configuration is missing in env.php.");
            return 4;
        }

        $payload = [
            'api_key'  => $apiKey,
            'zip_code' => $zip,
            'upc'      => $upc
        ];

        try {
            $this->curl->setHeaders(['Content-Type' => 'application/json']);
            $jsonPayload = json_encode($payload);

            if ($jsonPayload === false) {
                $this->logger->error('Failed to encode JSON payload: ' . json_last_error_msg());
                return 4;
            }

            $this->curl->post($apiUrl, $jsonPayload);
            $response  = $this->curl->getBody();
            $decoded   = json_decode($response, true);

            if (!isset($decoded['data']['response'])) {
                $this->logger->warning("Missing `response` key: " . $response);
                return 4;
            }

            $rawResponse = $decoded['data']['response'];

            /**
             * CASE 1: OLD FORMAT → INTEGER RESPONSE
             */
            if (is_int($rawResponse)) {
                if (in_array($rawResponse, [1, 2, 3, 5, 0], true)) {
                    $this->logger->info("Valid numeric response received: " . $rawResponse);
                    return $rawResponse;
                }

                $this->logger->warning("Unexpected numeric response: " . $rawResponse);
                return 4;
            }

            /**
             * CASE 2: NEW FORMAT → ARRAY OF ONE OBJECT
             * We return the full object wrapped under a key to preserve structure
             * during frontend/backend serialization cycles.
             */
            if (is_array($rawResponse)) {
                $firstItem = $rawResponse[0] ?? null;

                if (!$firstItem || !is_array($firstItem)) {
                    $this->logger->warning("Invalid object structure in Orchid array response");
                    return 4;
                }

                $this->logger->info("Orchid new object response received", $firstItem);

                // IMPORTANT: wrap under key so keys remain intact through serialization
                return ['orchid' => $firstItem];
            }

            /**
             * UNKNOWN RESPONSE FORMAT
             */
            $this->logger->warning("Unknown Orchid response format: " . $response);
            return 4;

        } catch (\Exception $e) {
            $this->logger->error("Estate API call failed: " . $e->getMessage());
            return 4;
        }
    }
}
