<?php
namespace Ahy\Caliber\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Psr\Log\LoggerInterface as Logger;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Webapi\Rest\Request;

class GetCaliberMemberCustomerDetails {
    /* The base url for the API. */
    const API_REQUEST_URI                                   = 'https://loyalty.yotpo.com/api/v2/';

    /* A constant that is used to call the API endpoint. */
    const API_CUSTOMER_DETAILS_ENDPOINT           = 'customers?';

    
    /* `API_REQUEST_KEY` is a constant that stores the API key required to make requests to the Bing Maps API.*/
    const API_REQUEST_KEY                                   = 'yIqMwwkC8zL0KFErjPPdLwtt';

    const API_GUID = 'orH6DSuP0ERk8fnmeJ-J9w';

    /**
     * @var logger
     */
    private $logger; 

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
     * This function is the constructor for the class. It takes in a bunch of parameters and assigns them to class variables
     * 
     * @param ClientFactory clientFactory is the factory class that will be used to create the client object.
     * @param ResponseFactory responseFactory is the factory class that will be used to create the response object.
     * @param Logger logger is the Magento logger.
     */
    public function __construct(
        ClientFactory           $clientFactory,
        ResponseFactory         $responseFactory,
        Logger                  $logger
    ) {
        $this->_clientFactory   = $clientFactory;
        $this->_responseFactory = $responseFactory;
        $this->logger           = $logger;
    }
    
    public function getCustomerDetails($customerEmail): ?array
    {
        $apiParams = self::API_CUSTOMER_DETAILS_ENDPOINT . 'customer_email=' . $customerEmail;
        $params             = [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-api-key' => self::API_REQUEST_KEY,
                'x-guid' => self::API_GUID
            ],
        ];
        $response           = $this->_doRequest($apiParams, Request::HTTP_METHOD_GET, $params);
        $status             = $response->getStatusCode(); // 200 status code
        $responseBody       = $response->getBody();
        $responseContent    = $responseBody->getContents(); // here you will have the API response in JSON format       
        $data               = json_decode($responseContent, true);
        return $data;
    }

    private function _doRequest(
        string  $uriEndpoint,
        string  $requestMethod,
        array   $params = []
        ): Response {
        $client = $this->_clientFactory->create([
            'config' => [
                'base_uri' => self::API_REQUEST_URI
                ]
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
                'reason' => $exception->getMessage()
            ]);
            $this->logger->info( 'In catch ' .  $exception->getMessage() . ' from Ahy\Ffl\Service\BingMapApi ');
        }
        return $response ;
    }
}
?>