<?php
namespace Ahy\BuyBox\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Psr\Log\LoggerInterface as Logger;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Webapi\Rest\Request;
use \Magento\Framework\Filesystem\DirectoryList;
use \Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Message\ManagerInterface;
// use Ahy\Ffl\Logger\Logger as FflApiLogger;

class UserGeoLocation {
    /* The base url for the API. */
    const API_CF_GEOLOCATION_URI                            = 'https://geo-cf-worker.everest.com/';

    /* The base url for the API. */
    const API_BING_MAP_API_BASE_URI                         = 'https://dev.virtualearth.net/REST/v1/';

    /* A constant that is used to call the API endpoint. */
    const API_CALCULATE_DISTANCE_REQUEST_ENDPOINT           = 'Routes/DistanceMatrix?key=';

    /* A constant that is used to call the API endpoint. */
    const API_GET_LAT_LONG_FROM_ADDRESS_REQUEST_ENDPOINT    = 'Locations?key=';
    
    /* `API_REQUEST_KEY` is a constant that stores the API key required to make requests to the Bing Maps API.*/
    const API_REQUEST_KEY                                   = 'ApGTO-V0eElZiHvosHcamRrL_NRFo0BRhCLSHnhmLxcT7abLf1L0rR2_-HFzWh_b';

    /**
     * Limit for the sellers data to be sent to the bing to calculate the distance between user and the sellers
     */
    const LIMIT_FOR_FFL_CENTER = 250;

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

     protected $messageManager;


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
        Logger                  $logger,
        ManagerInterface $messageManager
    ) {
        $this->_clientFactory   = $clientFactory;
        $this->_responseFactory = $responseFactory;
        $this->logger           = $logger;
        $this->messageManager = $messageManager;
    }

    public function getAddressCoordinates($userPinCode): ?array
    {
        $locationApi        = self::API_GET_LAT_LONG_FROM_ADDRESS_REQUEST_ENDPOINT . self::API_REQUEST_KEY . '&q=' . $userPinCode;
        $response           = $this->_doRequest(self::API_BING_MAP_API_BASE_URI, $locationApi, Request::HTTP_METHOD_GET, $params = []);
        $status             = $response->getStatusCode(); // 200 status code
        $responseBody       = $response->getBody();
        $responseContent    = $responseBody->getContents(); // here you will have the API response in JSON format       
        $data               = json_decode($responseContent, true);
        if (!empty($data['resourceSets'][0]['resources'][0])) {
            $latitude       = $data['resourceSets'][0]['resources'][0]['point']['coordinates'][0];
            $longitude      = $data['resourceSets'][0]['resources'][0]['point']['coordinates'][1];
            $returnLatLong  = [
                'latitude'  => $latitude,
                'longitude' => $longitude,
            ];
            return $returnLatLong;
        } else {
            return null;
        }
    }

    public function calculateFflDistanceFromUserInMiles($userAddressLatLongArr = [], $sellerLatLongData = [])
    {
        try {    
            $sellerLatLongCollection = array();
            $sellerLatLongArr = array();
            
            $userLatitude = isset($userAddressLatLongArr["latitude"]) ? $userAddressLatLongArr["latitude"] : null;
            $userLongitude = isset($userAddressLatLongArr["longitude"]) ? $userAddressLatLongArr["longitude"] : null;

            if ($userLatitude !== null && $userLongitude !== null) {
                $userLatLong        = [$userLatitude, $userLongitude ]; 

                // Output the nearest lat-longs
                $sellerLatitude = $sellerLatLongData['seller_latitude'];
                $sellerLongitude = $sellerLatLongData['seller_longitude'];
                $sellerLatLongArr[] = array(
                    'latitude'  => $sellerLatitude,
                    'longitude' => $sellerLongitude
                );
                $nearestLatLongs    = $this->findNearestLatLongs($userLatLong, $sellerLatLongArr, self::LIMIT_FOR_FFL_CENTER);
                $distanceMatrixApi  = self::API_CALCULATE_DISTANCE_REQUEST_ENDPOINT . self::API_REQUEST_KEY;
                $origins            = array(
                    array(
                        'latitude'  => $userLatitude,
                        'longitude' => $userLongitude
                    )
                );
                $destinations       = $sellerLatLongArr;
                $requestData        = array(
                    'origins'       => $origins,
                    'destinations'  => $destinations,
                    'travelMode'    => 'driving',
                    'distanceUnit'  => 'mi'
                );
                $body               = json_encode($requestData);
                $params             = [
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'body'          => $body 
                ];
                
                $response           = $this->_doRequest(self::API_BING_MAP_API_BASE_URI, $distanceMatrixApi, Request::HTTP_METHOD_POST, $params);
                $status             = $response->getStatusCode(); // 200 status code
                $responseBody       = $response->getBody();
                $responseContent    = $responseBody->getContents(); // here you will have the API response in JSON format);

                // Parse the response JSON
                $data               = json_decode($responseContent, true);
                
                if ($data !== null && isset($data['resourceSets'][0]['resources'][0]['results'])) {
                    // Extract the distances from the response
                    $distances      = $data['resourceSets'][0]['resources'][0]['results'];

                    usort($distances, function ($a, $b) {
                        if ($a['travelDistance'] == $b['travelDistance']) {
                            return 0;
                        }
                        return ($a['travelDistance'] < $b['travelDistance']) ? -1 : 1;
                    });

                    $travelDistances    = array_column($distances, 'travelDistance');
                    return $travelDistances;
                }
                return null;
            } else {
                // Handle the case when the latitude or longitude is null
                // Example: Log an error or display a user-friendly message
                $errorMessage = "Latitude or longitude is missing.";
                $this->logger->error($errorMessage);
                $userMessage = "An error occurred while calculating the FFL distance. Please provide a valid latitude and longitude.";
                $this->messageManager->addError($userMessage);
                return null;
            }
        } catch (\Exception $e) {
            // Handle the exception
            $errorMessage = $e->getMessage();
            $this->logger->error($errorMessage);
            $userMessage = "An error occurred while calculating the FFL distance. Please try again later.";
            $this->messageManager->addError($userMessage);
            return null;
        }
    }

    public function haversineDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius    = 6371; // Radius of the Earth in kilometers
        $deltaLat       = deg2rad($lat2 - $lat1);
        $deltaLon       = deg2rad($lon2 - $lon1);
        $a              = sin($deltaLat / 2) * sin($deltaLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($deltaLon / 2) * sin($deltaLon / 2);
        $c              = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance       = $earthRadius * $c;
        return $distance;
    }

    public function findNearestLatLongs($userLatLong, $fflCenterLatLong, $limit) {
        $distances          = [];
        foreach ($fflCenterLatLong as $latLong) {
            $distance       = $this->haversineDistance($userLatLong[0], $userLatLong[1], $latLong['latitude'], $latLong['longitude']);
            $distances[]    = $distance;
        }
        array_multisort($distances, $fflCenterLatLong); // Sort the fflCenterLatLong based on distances
        return array_slice($fflCenterLatLong, 0, $limit);
    }

    private function _doRequest(
        string  $baseUrl,
        string  $uriEndpoint,
        string  $requestMethod,
        array   $params = []
        ): Response {
        $client = $this->_clientFactory->create([
            'config' => [
                'base_uri' => $baseUrl
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
            $this->logger->info( 'In catch ' .  $exception->getMessage() . ' from Ahy\BuyBox\Service\UserGeolocation');
        }
        return $response ;
    }
}
?>