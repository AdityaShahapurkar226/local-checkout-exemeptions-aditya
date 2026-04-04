<?php

namespace Ahy\BarcodeLookup\Service;

use Ahy\BarcodeLookup\Logger\Logger as BarcodeLookupApiLogger;
use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\App\Config\ScopeConfigInterface;

class GetProductDetails
{

    const API_INVENTORY_PARENTS_REQUEST_ENDPOINT = 'v3/products?';

    const API_REQUEST_URI = 'https://api.barcodeLookup.com/';

    private $_apiKey;

    private $_defaultParam = '&formatted=y';
    /**
     * @var ResponseFactory
     *
     * A variable that is used to create the response object.
     */
    private $_responseFactory;

    /**
     * A variable that is used to create the client object.
     *
     * @var ClientFactory
     */
    private $_clientFactory;

    /**
     * @var BarcodeLookupApiLogger
     */
    private $_barcodeLookupApiLogger;

    /**
     * This function is the constructor for the class. It takes in a bunch of parameters and assigns them to class variables
     *
     * @param ClientFactory clientFactory is the factory class that will be used to create the client object.
     * @param ResponseFactory responseFactory is the factory class that will be used to create the response object.
     * @param BarcodeLookupApiLogger BarcodeLookupApiLogger is the class that will be used to log the API calls.
     */
    public function __construct(
        ClientFactory $clientFactory,
        ResponseFactory $responseFactory,
        BarcodeLookupApiLogger $barcodeLookupApiLogger,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->_clientFactory          = $clientFactory;
        $this->_responseFactory        = $responseFactory;
        $this->_barcodeLookupApiLogger = $barcodeLookupApiLogger;
        $this->_apiKey = '&key=' . $scopeConfig->getValue(
            'barcode_lookup/general/api_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getProductData($upcs): array
    {
        $this->_barcodeLookupApiLogger->info('UPCs before trimming ' . $upcs);

        $upcs = $this->cleanUpcs($upcs);

        $this->_barcodeLookupApiLogger->info('UPCs after trimming ' . $upcs);

        try {
            $responseContent = [];
            // Make a request to the barcode API
            $updatedUpc = (string) 'barcode=' . $upcs;
            $barcodeApi = self::API_INVENTORY_PARENTS_REQUEST_ENDPOINT . $updatedUpc . $this->_defaultParam . $this->_apiKey;
            var_dump($barcodeApi);
            $params    = [
                'headers' => [
                    'Accept'      => 'application/json',
                ],
            ];
            $response = $this->_doRequest(uriEndpoint: $barcodeApi, requestMethod: Request::HTTP_METHOD_GET, params: $params);
            // var_dump($response);
            $status = $response->getStatusCode(); // 200 status code
            $responseContent['status'] = $status;
            $responseBody = $response->getBody();
            $responseContent['response'] = $responseBody->getContents(); // here you will have the API response in JSON format
            return $responseContent;
        } catch (\Exception $e) {
            $errorMessage   = $e->getMessage();
            $errorFile      = $e->getFile();
            $errorLine      = $e->getLine();
            $this->_barcodeLookupApiLogger->error(' In catch ' . $errorMessage . 'from ' . $errorFile . ':' . $errorLine . 'in Ahy\BarcodeLookup\Service\GetProductDetails');
            // Log the exception and re-throw it to propagate the error
            $this->_barcodeLookupApiLogger->error(' In catch ' . $e->getMessage() . ' from Ahy\BarcodeLookup\Service\GetProductDetails');

            throw $e;
        }
    }

    private function _doRequest(string $uriEndpoint, string $requestMethod, array $params = []): Response
    {
        $client = $this->_clientFactory->create([
            'config' => [
                'base_uri' => self::API_REQUEST_URI,
            ],
        ]);
        try {
            $response = $client->request(
                $requestMethod,
                $uriEndpoint,
                $params
            );
        } catch (GuzzleException $exception) {
            $response = $this->_responseFactory->create([
                'status' => $exception->getCode(),
                'reason' => $exception->getMessage(),
            ]);
            $this->_barcodeLookupApiLogger->info(' In catch ' . $exception->getMessage() . ' from Ahy\BarcodeLookup\Service\GetProductDetails');
        }
        return $response;
    }

    /**
     * Cleans a comma-separated UPC string by trimming all kinds of whitespace from each entry.
     *
     * @param string|null $upcs
     * @return string
     */
    function cleanUpcs(?string $upcs): string
    {
        if (empty($upcs)) {
            return '';
        }

        $upcArray = explode(',', $upcs);

        // Normalize spaces using a Unicode-aware regex
        $cleanArray = array_map(function ($upc) {
            // Remove all Unicode whitespace characters including non-breaking space
            return preg_replace('/\s+/u', '', $upc);
        }, $upcArray);

        return implode(',', $cleanArray);
    }

}