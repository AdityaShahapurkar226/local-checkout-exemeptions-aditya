<?php
namespace Ahy\Ffl\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Psr\Log\LoggerInterface as Logger;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Webapi\Rest\Request;
use \Magento\Framework\Filesystem\DirectoryList;
use \Magento\Framework\Serialize\Serializer\Json;
// use Ahy\Ffl\Logger\Logger as FflApiLogger;

class BingMapsApi {
    /* The base url for the API. */
    const API_REQUEST_URI                                   = 'https://dev.virtualearth.net/REST/v1/';

    /* A constant that is used to call the API endpoint. */
    const API_CALCULATE_DISTANCE_REQUEST_ENDPOINT           = 'Routes/DistanceMatrix?key=';

    /* A constant that is used to call the API endpoint. */
    const API_GET_LAT_LONG_FROM_ADDRESS_REQUEST_ENDPOINT    = 'Locations?key=';
    
    /* `API_REQUEST_KEY` is a constant that stores the API key required to make requests to the Bing Maps API.*/
    const API_REQUEST_KEY                                   = 'ApGTO-V0eElZiHvosHcamRrL_NRFo0BRhCLSHnhmLxcT7abLf1L0rR2_-HFzWh_b';

    /**
     * Limit for the ffl center to be sent to the bing to calculate the distance between user and the ffl center
     */
    const LIMIT_FOR_FFL_CENTER = 250;

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
     * @var FflApiLogger
     */
    // private $_fflApiLogger;

    /**
     * This function is the constructor for the class. It takes in a bunch of parameters and assigns them to class variables
     * 
     * @param ClientFactory clientFactory is the factory class that will be used to create the client object.
     * @param ResponseFactory responseFactory is the factory class that will be used to create the response object.
     * @param DirectoryList dir is the Magento directory list object.
     * @param Json json is the Magento Json class.
     * @param Logger logger is the Magento logger.
     * @param FflApiLogger fflApiLogger is the class that will be used to log the API calls.
     */
    public function __construct(
        ClientFactory           $clientFactory,
        ResponseFactory         $responseFactory,
        DirectoryList           $dir,
        Json                    $json,
        Logger                  $logger
    ) {
        $this->_clientFactory   = $clientFactory;
        $this->_responseFactory = $responseFactory;
        $this->_dir             = $dir;
        $this->_json            = $json;
        $this->logger           = $logger;
    }
    
    public function getAddressCoordinates($addressArr): ?array
    {
        $userDetailsForFfl  = implode(', ', $addressArr);
        $locationApi        = self::API_GET_LAT_LONG_FROM_ADDRESS_REQUEST_ENDPOINT . self::API_REQUEST_KEY . '&q=' . $userDetailsForFfl;
        $response           = $this->_doRequest($locationApi, Request::HTTP_METHOD_GET, $params = []);
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

    public function calculateFflDistanceFromUserInMiles($userAddressLatLongArr = [], $FflCenterAddressArr = [])
    {
        $FflCenterLatLongCollection = array();
        $FflCenterLatLongArr = array();
        
        foreach ($FflCenterAddressArr as $coordinates) {
            if ($coordinates['lat'] !== null && $coordinates['long'] !== null) {
                $FflCenterLatLongCollection[] = array(
                    'latitude'  => $coordinates['lat'],
                    'longitude' => $coordinates['long']
                );
            }
        }
        
        $userLatitude       = $userAddressLatLongArr["latitude"];
        $userLongitude      = $userAddressLatLongArr["longitude"];
        $userLatLong        = [$userLatitude, $userLongitude ]; 

        $nearestLatLongs    = $this->findNearestLatLongs($userLatLong, $FflCenterLatLongCollection, self::LIMIT_FOR_FFL_CENTER);
        // Output the nearest lat-longs
        foreach ($nearestLatLongs as $latLong) {
            $FflCenterLatLongArr[] = array(
                'latitude'  => $latLong['latitude'],
                'longitude' => $latLong['longitude']
            );
        }

        $distanceMatrixApi  = self::API_CALCULATE_DISTANCE_REQUEST_ENDPOINT . self::API_REQUEST_KEY;
        $origins            = array(
            array(
                'latitude'  => $userLatitude,
                'longitude' => $userLongitude
            )
        );
        $destinations       = $FflCenterLatLongArr;
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
        
        $response           = $this->_doRequest($distanceMatrixApi, Request::HTTP_METHOD_POST, $params);
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
    }

    public function haversineDistance($lat1, $lon1, $lat2, $lon2) {
        $lat1 = floatval($lat1);
        $lon1 = floatval($lon1);
        $lat2 = floatval($lat2);
        $lon2 = floatval($lon2);

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