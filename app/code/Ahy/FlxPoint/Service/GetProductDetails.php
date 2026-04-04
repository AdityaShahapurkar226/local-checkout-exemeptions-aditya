<?php
namespace Ahy\FlxPoint\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Psr\Log\LoggerInterface as Logger;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Webapi\Rest\Request;
use \Magento\Framework\Filesystem\DirectoryList;
use \Magento\Framework\Serialize\Serializer\Json;
use Ahy\FlxPoint\Logger\Logger as FlxPointApiLogger;

class GetProductDetails {
    
    const API_PRODUCT_REQUEST_ENDPOINT = 'product/parents?';
    
    const API_INVENTORY_PARENTS_REQUEST_ENDPOINT = 'inventory/parents?';

    const API_REQUEST_URI = 'https://api.flxpoint.com/';
    
    private $_apiToken = 'Ww2iFJ6ahpwIDYF6M4OXiKfnPeN9oIvNKyeeAgd8RuIs5J376AJ6wXzeG9mE2FeC9tSqHI29oVbd9pTQscPJTW2zZ0vQjEebXpum';
    
    /**
     * @var logger
     */
    private $logger; 

    /**
     * variable that is used to get the directory list. 
     *
     * @var DirectoryList
     */
    protected $_dir;

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
     * A variable that is used to store the body of the request.
     *
     * @var Array
     */
    private $_body;

    /**
     * Used to serialize the data
     *
     * @var Json
     */
    private $_json;

    /**
     * @var FlxPointApiLogger
     */
    private $_flxPointApiLogger;

    /**
     * This function is the constructor for the class. It takes in a bunch of parameters and assigns them to class variables
     * 
     * @param ClientFactory clientFactory is the factory class that will be used to create the client object.
     * @param ResponseFactory responseFactory is the factory class that will be used to create the response object.
     * @param DirectoryList dir is the Magento directory list object.
     * @param Json json is the Magento Json class.
     * @param Logger logger is the Magento logger.
     * @param FlxPointApiLogger flxPointApiLogger is the class that will be used to log the API calls.
     */
    public function __construct(
        ClientFactory           $clientFactory,
        ResponseFactory         $responseFactory,
        DirectoryList           $dir,
        Json                    $json,
        Logger                  $logger,
        FlxPointApiLogger       $flxPointApiLogger
    ) {
        $this->_clientFactory   = $clientFactory;
        $this->_responseFactory = $responseFactory;
        $this->_dir             = $dir;
        $this->_json            = $json;
        $this->logger           = $logger;
        $this->_flxPointApiLogger     = $flxPointApiLogger;
    }

    public function getInventoryParentAndVariants($pageNumber, $lastUpdatedAt, $sellerId){
        if($lastUpdatedAt) {
            $lastUpdatedAt = urlencode($lastUpdatedAt);
        }
        $productParams  = "sourceId=$sellerId
                            &pageSize=100
                            &includeVariants=true
                            &includeImages=true
                            &includeOptions=true
                            &includeAttributes=true
                            &includeCategories=true
                            &includeCustomFields=true
                            &includeCustomAggregates=true
                            &includeLinkedProductVariants=true
                            &updatedAfter=$lastUpdatedAt
                            &page=$pageNumber";
        $vendorApi = self::API_INVENTORY_PARENTS_REQUEST_ENDPOINT . $productParams;
        $params = [
            'headers' => [
                'Accept'        => 'application/json',
                'X-API-TOKEN'   => $this->_apiToken,
            ],
        ];
        $response = $this->_doRequest($vendorApi, Request::HTTP_METHOD_GET, $params);
        $status = $response->getStatusCode(); // 200 status code
        $responseBody = $response->getBody();
        $responseContent = $responseBody->getContents(); // here you will have the API response in JSON format
        return $responseContent;
    }
    
    private function _doRequest( string  $uriEndpoint,  string  $requestMethod,  array   $params = [] ): Response {
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
            $this->_flxPointApiLogger->info( ' In catch ' . ' <pre> ' . $exception->getMessage() . ' from Ahy\FlxPoint\Service\GetProductDetails');
        }
        return $response ;
    }

}