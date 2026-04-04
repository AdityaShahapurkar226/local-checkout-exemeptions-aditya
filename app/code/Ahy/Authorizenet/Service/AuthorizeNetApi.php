<?php

namespace Ahy\Authorizenet\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientFactory;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ResponseFactory;
use Psr\Log\LoggerInterface as Logger;
use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Webapi\Rest\Request;
use \Magento\Framework\Filesystem\DirectoryList;
use \Magento\Framework\Serialize\Serializer\Json;
use Ahy\Authorizenet\Logger\Logger as ApiLogger;
use Ahy\Authorizenet\Helper\Data;
use Magento\Framework\Session\SessionManagerInterface;
use Ahy\Authorizenet\Logger\SavedCCFrontendLogger;
use Ahy\Authorizenet\Logger\SavedCCAdminLogger;


class AuthorizeNetApi
{


    /* The base url for the API. */
    const API_REQUEST_URI_SANDBOX                                   = 'https://apitest.authorize.net/xml/v1/request.api';

    const API_REQUEST_URI_LIVE                                      = 'https://api.authorize.net/xml/v1/request.api';

    /* A constant that is used to call the API endpoint. */
    const API_TRANSACTION_KEY                                       = '2XvaHQ5r5u57NM2m';

    /* API request endpoint*/
    const API_LOGIN_KEY                                             = '8e4y98EcJF7c';

    public $isSandbox = true;

    /**
     * @var logger
     */
    private $logger;
    /**
     * @var Data
     */
    private $_helper;

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
     * Used to serialize the data
     *
     * @var Json
     */
    private $_json;

    /**
     * @var ApiLogger
     */
    private $_ApiLogger;
    protected SavedCCFrontendLogger $savedCCFrontendLogger;
    protected SavedCCAdminLogger $savedCCAdminLogger;
    /**
     * @var SessionManagerInterface
     */
    private $session;

    /**
     * This function is the constructor for the class. It takes in a bunch of parameters and assigns them to class variables
     * 
     * @param ClientFactory clientFactory is the factory class that will be used to create the client object.
     * @param ResponseFactory responseFactory is the factory class that will be used to create the response object.
     * @param DirectoryList dir is the Magento directory list object.
     * @param Json json is the Magento Json class.
     * @param Logger logger is the Magento logger.
     * @param ApiLogger ApiLogger is the class that will be used to log the API calls.
     */
    public function __construct(
        ClientFactory           $clientFactory,
        ResponseFactory         $responseFactory,
        DirectoryList           $dir,
        Json                    $json,
        Logger                  $logger,
        Data                    $helper,
        ApiLogger               $apiLogger,
        SessionManagerInterface $session,
        SavedCCFrontendLogger $savedCCFrontendLogger,
        SavedCCAdminLogger $savedCCAdminLogger
    ) {
        $this->_clientFactory   = $clientFactory;
        $this->_responseFactory = $responseFactory;
        $this->_dir             = $dir;
        $this->_json            = $json;
        $this->_helper          = $helper;
        $this->logger           = $logger;
        $this->_ApiLogger       = $apiLogger;
        $this->session          = $session;
        $this->savedCCFrontendLogger = $savedCCFrontendLogger;
        $this->savedCCAdminLogger = $savedCCAdminLogger;
    }

    protected function logSavedCC(string $message, array $context = []): void
    {
        try {
            $areaCode = $this->appState->getAreaCode();
        } catch (\Exception $e) {
            $areaCode = 'unknown';
        }

        switch ($areaCode) {
            case \Magento\Framework\App\Area::AREA_ADMINHTML:
                $this->savedCCAdminLogger->info($message, $context);
                break;

            case \Magento\Framework\App\Area::AREA_FRONTEND:
                $this->savedCCFrontendLogger->info($message, $context);
                break;

            default:
                $this->savedCCFrontendLogger->info('[CHECKOUT] ' . $message, $context);
                break;
        }
    }

    public function createCharge($cardNumber, $expireMonth, $expireYear, $cardCvv, $amount, $shippingAddressArray, $billingAddressArray, $customerDetailsArray)
    {
        // return [$cardNumber, $expireMonth, $expireYear, $cardCvv, $amount];
        $apiCredentials = $this->getApiCredentials();
        $expirationDate = $expireYear . '-' . $expireMonth;
        // shipping details
        $shippingCity           = !empty($shippingAddressArray) && isset($shippingAddressArray['city']) ? $shippingAddressArray['city'] : 'Not Available';
        $shippingStreet         = !empty($shippingAddressArray) && isset($shippingAddressArray['street']) ? $shippingAddressArray['street'] : 'Not Available';
        $shippingRegion         = !empty($shippingAddressArray) && isset($shippingAddressArray['region']) ? $shippingAddressArray['region'] : 'Not Available';
        $shippingLastName       = !empty($shippingAddressArray) && isset($shippingAddressArray['lastName']) ? $shippingAddressArray['lastName'] : 'Not Available';
        $shippingPostCode       = !empty($shippingAddressArray) && isset($shippingAddressArray['postcode']) ? $shippingAddressArray['postcode'] : 'Not Available';
        $shippingFirstName      = !empty($shippingAddressArray) && isset($shippingAddressArray['firstName']) ? $shippingAddressArray['firstName'] : 'Not Available';
        $shippingCountryCode    = !empty($shippingAddressArray) && isset($shippingAddressArray['country_code']) ? $shippingAddressArray['country_code'] : 'Not Available';
        // billing details
        $billingCity            = !empty($billingAddressArray) && isset($billingAddressArray['city']) ? $billingAddressArray['city'] : 'Not Available';
        $billingRegion          = !empty($billingAddressArray) && isset($billingAddressArray['region']) ? $billingAddressArray['region'] : 'Not Available';
        $billingStreet          = !empty($billingAddressArray) && isset($billingAddressArray['street']) ? $billingAddressArray['street'] : 'Not Available';
        $billingLastName        = !empty($billingAddressArray) && isset($billingAddressArray['lastName']) ? $billingAddressArray['lastName'] : 'Not Available';
        $billingFirstName       = !empty($billingAddressArray) && isset($billingAddressArray['firstName']) ? $billingAddressArray['firstName'] : 'Not Available';
        $billingPostCode        = !empty($billingAddressArray) && isset($billingAddressArray['postcode']) ? $billingAddressArray['postcode'] : 'Not Available';
        $billingTelephone       = !empty($billingAddressArray) && isset($billingAddressArray['telephone']) ? $billingAddressArray['telephone'] : 'Not Available';
        $billingCountryCode     = !empty($billingAddressArray) && isset($billingAddressArray['country_code']) ? $billingAddressArray['country_code'] : 'Not Available';
        // customer details
        $customerId             = !empty($customerDetailsArray) && isset($customerDetailsArray['customerId']) ? $customerDetailsArray['customerId'] : 'Not Available';
        $customerEmail          = !empty($customerDetailsArray) && isset($customerDetailsArray['email']) ? $customerDetailsArray['email'] : 'Not Available';

        // Store actual card info into session for later use in CIM profile creation
        $this->session->setData('authnet_card_number', $cardNumber);
        $this->session->setData('authnet_expiration_date', $expirationDate);

        $body = '
            {
                "createTransactionRequest": {
                    "merchantAuthentication": {
                        "name": "' . $apiCredentials['apiKey'] . '",
                        "transactionKey": "' . $apiCredentials['transactionKey'] . '"
                    },
                    "transactionRequest": {
                        "transactionType": "authCaptureTransaction",
                        "amount": "' . ($amount !== null ? number_format($amount, 2, '.', '') : '0.00') . '",
                        "payment": {
                            "creditCard": {
                                "cardNumber": "' . $cardNumber . '",
                                "expirationDate": "' . $expirationDate . '",
                                "cardCode": "' . $cardCvv . '"
                            }
                        },
                        "customer":{
                            "type": "individual",
                            "id": "' . $customerId . '",
                            "email": "' . $customerEmail . '"
                        },
                        "billTo": {
                            "firstName": "' . $billingFirstName . '",
                            "lastName": "' . $billingLastName . '",
                            "address": "' . $billingStreet . '",
                            "city": "' . $billingCity . '",
                            "state": "' . $billingRegion . '",
                            "zip": "' . $billingPostCode . '",
                            "country": "' . $billingCountryCode . '",
                            "phoneNumber": "' . $billingTelephone . '"
                            
                        },
                        "shipTo": {
                            "firstName": "' . $shippingFirstName . '",
                            "lastName": "' . $shippingLastName . '",
                            "address": "' . $shippingStreet . '",
                            "city": "' . $shippingCity . '",
                            "state": "' . $shippingRegion . '",
                            "zip": "' . $shippingPostCode . '",
                            "country": "' . $shippingCountryCode . '"
                        }
                    }
                }
            }';
        // $this->_ApiLogger->info($body);
        $params             = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body'          => $body
        ];
        $response           = $this->_doRequest($chargeApiEndpoint = '', Request::HTTP_METHOD_POST, $params);
        $status             = $response->getStatusCode(); // 200 status code
        $responseBody       = $response->getBody();
        $responseContent    = $responseBody->getContents(); // here you will have the API response in JSON format
        $responseContent    = $this->removeByteOrderMarkFromJsonString($responseContent);

        return $responseContent;
    }

    public function authorizeCard($cardNumber, $expireMonth, $expireYear, $cardCvv, $amount, $shippingAddressArray, $billingAddressArray, $customerDetailsArray)
    {
        // return [$cardNumber, $expireMonth, $expireYear, $cardCvv, $amount];
        $apiCredentials = $this->getApiCredentials();
        $expirationDate = $expireYear . '-' . $expireMonth;
        // shipping details
        $shippingCity           = !empty($shippingAddressArray) && isset($shippingAddressArray['city']) ? $shippingAddressArray['city'] : 'Not Available';
        $shippingStreet         = !empty($shippingAddressArray) && isset($shippingAddressArray['street']) ? $shippingAddressArray['street'] : 'Not Available';
        $shippingRegion         = !empty($shippingAddressArray) && isset($shippingAddressArray['region']) ? $shippingAddressArray['region'] : 'Not Available';
        $shippingLastName       = !empty($shippingAddressArray) && isset($shippingAddressArray['lastName']) ? $shippingAddressArray['lastName'] : 'Not Available';
        $shippingPostCode       = !empty($shippingAddressArray) && isset($shippingAddressArray['postcode']) ? $shippingAddressArray['postcode'] : 'Not Available';
        $shippingFirstName      = !empty($shippingAddressArray) && isset($shippingAddressArray['firstName']) ? $shippingAddressArray['firstName'] : 'Not Available';
        $shippingCountryCode    = !empty($shippingAddressArray) && isset($shippingAddressArray['country_code']) ? $shippingAddressArray['country_code'] : 'Not Available';
        // billing details
        $billingCity            = !empty($billingAddressArray) && isset($billingAddressArray['city']) ? $billingAddressArray['city'] : 'Not Available';
        $billingRegion          = !empty($billingAddressArray) && isset($billingAddressArray['region']) ? $billingAddressArray['region'] : 'Not Available';
        $billingStreet          = !empty($billingAddressArray) && isset($billingAddressArray['street']) ? $billingAddressArray['street'] : 'Not Available';
        $billingLastName        = !empty($billingAddressArray) && isset($billingAddressArray['lastName']) ? $billingAddressArray['lastName'] : 'Not Available';
        $billingFirstName       = !empty($billingAddressArray) && isset($billingAddressArray['firstName']) ? $billingAddressArray['firstName'] : 'Not Available';
        $billingPostCode        = !empty($billingAddressArray) && isset($billingAddressArray['postcode']) ? $billingAddressArray['postcode'] : 'Not Available';
        $billingTelephone       = !empty($billingAddressArray) && isset($billingAddressArray['telephone']) ? $billingAddressArray['telephone'] : 'Not Available';
        $billingCountryCode     = !empty($billingAddressArray) && isset($billingAddressArray['country_code']) ? $billingAddressArray['country_code'] : 'Not Available';
        // customer details
        $customerId             = !empty($customerDetailsArray) && isset($customerDetailsArray['customerId']) ? $customerDetailsArray['customerId'] : 'Not Available';
        $customerEmail          = !empty($customerDetailsArray) && isset($customerDetailsArray['email']) ? $customerDetailsArray['email'] : 'Not Available';
        $this->session->setData('authnet_card_number', $cardNumber);
        $this->session->setData('authnet_expiration_date', $expirationDate);


        $body = '
            {
                "createTransactionRequest": {
                    "merchantAuthentication": {
                        "name": "' . $apiCredentials['apiKey'] . '",
                        "transactionKey": "' . $apiCredentials['transactionKey'] . '"
                    },
                    "transactionRequest": {
                        "transactionType": "authOnlyTransaction",
                        "amount": "' . ($amount !== null ? number_format($amount, 2, '.', '') : '0.00') . '",
                        "payment": {
                            "creditCard": {
                                "cardNumber": "' . $cardNumber . '",
                                "expirationDate": "' . $expirationDate . '",
                                "cardCode": "' . $cardCvv . '"
                            }
                        },
                        "customer":{
                            "type": "individual",
                            "id": "' . $customerId . '",
                            "email": "' . $customerEmail . '"
                        },
                        "billTo": {
                            "firstName": "' . $billingFirstName . '",
                            "lastName": "' . $billingLastName . '",
                            "address": "' . $billingStreet . '",
                            "city": "' . $billingCity . '",
                            "state": "' . $billingRegion . '",
                            "zip": "' . $billingPostCode . '",
                            "country": "' . $billingCountryCode . '",
                            "phoneNumber": "' . $billingTelephone . '"
                            
                        },
                        "shipTo": {
                            "firstName": "' . $shippingFirstName . '",
                            "lastName": "' . $shippingLastName . '",
                            "address": "' . $shippingStreet . '",
                            "city": "' . $shippingCity . '",
                            "state": "' . $shippingRegion . '",
                            "zip": "' . $shippingPostCode . '",
                            "country": "' . $shippingCountryCode . '"
                        }
                    }
                }
            }';
        // $this->_ApiLogger->info($body);
        $params             = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body'          => $body
        ];
        $response           = $this->_doRequest($chargeApiEndpoint = '', Request::HTTP_METHOD_POST, $params);
        $status             = $response->getStatusCode(); // 200 status code
        $responseBody       = $response->getBody();
        $responseContent    = $responseBody->getContents(); // here you will have the API response in JSON format
        $responseContent    = $this->removeByteOrderMarkFromJsonString($responseContent);

        return $responseContent;
    }

    // public function createCustomerProfileFromTransaction(string $transactionId, string $email, string $merchantCustomerId)
    // {
    //     $apiCredentials = $this->getApiCredentials();

    //     $body = [
    //         'createCustomerProfileFromTransactionRequest' => [
    //             'merchantAuthentication' => [
    //                 'name' => $apiCredentials['apiKey'],
    //                 'transactionKey' => $apiCredentials['transactionKey']
    //             ],
    //             'transId' => $transactionId,
    //             'customer' => [
    //                 'merchantCustomerId' => $merchantCustomerId,
    //                 'email' => $email,
    //             ]
    //         ]
    //     ];

    //     $this->_ApiLogger->info('Create Customer Profile From Transaction Request: ' . $this->_json->serialize($body));
    //     $this->logSavedCC('Create Customer Profile From Transaction Request: ' . $this->_json->serialize($body));

    //     $params = [
    //         'headers' => [
    //             'Content-Type' => 'application/json',
    //         ],
    //         'body' => $this->_json->serialize($body)
    //     ];

    //     try {
    //         $response = $this->_doRequest('', Request::HTTP_METHOD_POST, $params);
    //         $status = $response->getStatusCode();
    //         $responseContent = $this->removeByteOrderMarkFromJsonString($response->getBody()->getContents());

    //         $this->_ApiLogger->info('Create Customer Profile From Transaction Response: ' . $responseContent);
    //         $this->logSavedCC('Create Customer Profile From Transaction Response: ' . $responseContent);

    //         return $responseContent;
    //     } catch (\Exception $e) {
    //         $this->_ApiLogger->error('Error in createCustomerProfileFromTransaction: ' . $e->getMessage());
    //         $this->logSavedCC('Error in createCustomerProfileFromTransaction: ' . $e->getMessage());
    //         throw new \Exception(__('Authorize.Net: Could not create customer profile from transaction.'));
    //     }
    // }

    public function createCustomerProfileFromTransaction(
        string $transactionId,
        string $email,
        string $merchantCustomerId
    ) {
        $apiCredentials = $this->getApiCredentials();

        // If email/customerId not provided, try to get from session
        if (empty($email)) {
            $email = $this->session->getData('customer_email') ?? 'customer@example.com';
        }
        if (empty($merchantCustomerId)) {
            $merchantCustomerId = $this->session->getData('customer_id') ?? 'guest_' . time();
        }

        $body = [
            'createCustomerProfileFromTransactionRequest' => [
                'merchantAuthentication' => [
                    'name' => $apiCredentials['apiKey'],
                    'transactionKey' => $apiCredentials['transactionKey']
                ],
                'transId' => $transactionId,
                'customer' => [
                    'merchantCustomerId' => $merchantCustomerId,
                    'email' => $email,
                ]
            ]
        ];

        $this->_ApiLogger->info('Create Customer Profile From Transaction Request: ' . $this->_json->serialize($body));
        $this->logSavedCC('Create Customer Profile From Transaction Request: ' . $this->_json->serialize($body));

        $params = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => $this->_json->serialize($body)
        ];

        try {
            $response = $this->_doRequest('', Request::HTTP_METHOD_POST, $params);
            $status = $response->getStatusCode();
            $responseContent = $this->removeByteOrderMarkFromJsonString($response->getBody()->getContents());

            $this->_ApiLogger->info('Create Customer Profile From Transaction Response: ' . $responseContent);
            $this->logSavedCC('Create Customer Profile From Transaction Response: ' . $responseContent);

            return $responseContent;
        } catch (\Exception $e) {
            $this->_ApiLogger->error('Error in createCustomerProfileFromTransaction: ' . $e->getMessage());
            $this->logSavedCC('Error in createCustomerProfileFromTransaction: ' . $e->getMessage());
            throw new \Exception(__('Authorize.Net: Could not create customer profile from transaction.'));
        }
    }

    public function createCustomerProfile(array $data): array
    {
        $apiCredentials = $this->getApiCredentials();

        /* Build full payload */
        $payload = [
            'createCustomerProfileRequest' => [
                'merchantAuthentication' => [
                    'name'           => $apiCredentials['apiKey'],
                    'transactionKey' => $apiCredentials['transactionKey'],
                ],
                'profile' => [
                    'merchantCustomerId' => $data['merchant_customer_id'] ?? '',
                    'email'              => $data['email']              ?? '',
                    'paymentProfiles'    => [
                        [
                            'customerType' => 'individual',
                            'billTo' => [
                                'firstName'   => $data['billing_first_name'] ?? '',
                                'lastName'    => $data['billing_last_name']  ?? '',
                                'address'     => $data['billing_street']     ?? '',
                                'city'        => $data['billing_city']       ?? '',
                                'state'       => $data['billing_state']      ?? '',
                                'zip'         => $data['billing_zip']        ?? '',
                                'country'     => $data['billing_country']    ?? '',
                                'phoneNumber' => $data['billing_phone']      ?? '',
                            ],
                            'payment' => [
                                'creditCard' => [
                                    'cardNumber'     => $data['card_number'],
                                    'expirationDate' => $data['expiration_date'],
                                    'cardCode'       => $data['cvv'],
                                ],
                            ],
                            'defaultPaymentProfile' => true,
                        ],
                    ],
                ],
                'validationMode' => $data['validation_mode'] ?? 'testMode',
            ],
        ];

        $jsonPayload = $this->_json->serialize($payload);
        $this->_ApiLogger->info('[AuthorizeNetApi] Payload for createCustomerProfileRequest: ' . $jsonPayload);
        $this->logSavedCC('[AuthorizeNetApi] Payload for createCustomerProfileRequest: ' . $jsonPayload);

        /* Call API */
        try {
            $response = $this->_doRequest(
                '',
                Request::HTTP_METHOD_POST,
                [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body'    => $jsonPayload,
                ]
            );

            $decoded = $this->_json->unserialize(
                $this->removeByteOrderMarkFromJsonString($response->getBody()->getContents())
            );

            if (
                !isset($decoded['messages']['resultCode']) ||
                $decoded['messages']['resultCode'] !== 'Ok'
            ) {
                $message = $decoded['messages']['message'][0]['text'] ?? 'Unknown error';
                return ['success' => false, 'message' => $message];
            }

            return [
                'success'             => true,
                'customer_profile_id' => $decoded['customerProfileId']        ?? null,
                'payment_profile_id'  => $decoded['customerPaymentProfileIdList'][0] ?? null,
            ];
        } catch (\Exception $e) {
            $this->_ApiLogger->error('[AuthorizeNetApi] createCustomerProfile error: ' . $e->getMessage());
            $this->logSavedCC('[AuthorizeNetApi] createCustomerProfile error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function createCustomerPaymentProfile(string $customerProfileId, array $profileData): array
    {
        $apiCredentials = $this->getApiCredentials();
        $this->logger->info('[AuthorizeNetApi] Final payload before calling createCustomerPaymentProfile: ' . $this->_json->serialize($profileData));
        $this->logSavedCC('[AuthorizeNetApi] Final payload before calling createCustomerPaymentProfile: ' . $this->_json->serialize($profileData));
        $payload = [
            'createCustomerPaymentProfileRequest' => [
                'merchantAuthentication' => [
                    'name'           => $apiCredentials['apiKey'],
                    'transactionKey' => $apiCredentials['transactionKey'],
                ],
                'customerProfileId' => $customerProfileId,
                'paymentProfile' => [
                    'billTo' => [
                        'firstName'   => $profileData['billTo']['firstName']   ?? '',
                        'lastName'    => $profileData['billTo']['lastName']    ?? '',
                        'address'     => $profileData['billTo']['address']     ?? '',
                        'city'        => $profileData['billTo']['city']        ?? '',
                        'state'       => $profileData['billTo']['state']       ?? '',
                        'zip'         => $profileData['billTo']['zip']         ?? '',
                        'country'     => $profileData['billTo']['country']     ?? '',
                        'phoneNumber' => $profileData['billTo']['phoneNumber'] ?? '',
                    ],
                    'payment' => [
                        'creditCard' => [
                            'cardNumber'     => $profileData['payment']['creditCard']['cardNumber']     ?? '',
                            'expirationDate' => $profileData['payment']['creditCard']['expirationDate'] ?? '',
                        ],
                    ],
                    'defaultPaymentProfile' => true,
                ],
                'validationMode' => 'testMode', // or 'liveMode' in production
            ],
        ];

        $jsonPayload = $this->_json->serialize($payload);
        $this->logSavedCC('[AuthorizeNetApi] Payload for createCustomerPaymentProfileRequest: ' . $jsonPayload);
        $this->logger->info('[AuthorizeNetApi] Payload for createCustomerPaymentProfileRequest: ' . $jsonPayload);

        try {
            $response = $this->_doRequest(
                '',
                Request::HTTP_METHOD_POST,
                [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body'    => $jsonPayload,
                ]
            );

            $decoded = $this->_json->unserialize(
                $this->removeByteOrderMarkFromJsonString($response->getBody()->getContents())
            );

            if (
                isset($decoded['messages']['resultCode']) &&
                $decoded['messages']['resultCode'] === 'Ok'
            ) {
                return [
                    'success' => true,
                    'customerPaymentProfileId' => $decoded['customerPaymentProfileId'] ?? null,
                ];
            }

            // Handle known Authorize.Net error messages
            $messages = $decoded['messages']['message'] ?? [];
            foreach ($messages as $error) {
                $code = $error['code'] ?? '';
                $text = $error['text'] ?? '';

                $this->logger->error('[AuthorizeNetApi] Error response: [' . $code . '] ' . $text);
                $this->logSavedCC('[AuthorizeNetApi] Error response: [' . $code . '] ' . $text);

                // Message for card limit reached
                if ($code === 'E00042' || stripos($text, 'maximum number') !== false) {
                    return [
                        'success' => false,
                        'message' => __('You have reached the maximum number of saved cards (10). Please delete an existing card to add a new one.')
                    ];
                }
            }

            $message = $decoded['messages']['message'][0]['text'] ?? 'Unknown error';
            return ['success' => false, 'message' => $message];
        } catch (\Exception $e) {
            $this->_ApiLogger->error('[AuthorizeNetApi] createCustomerPaymentProfile error: ' . $e->getMessage());
            $this->logSavedCC('[AuthorizeNetApi] createCustomerPaymentProfile error: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getTransactionDetails(string $transactionId): string
    {
        $apiCredentials = $this->getApiCredentials();
        $payload = [
            'getTransactionDetailsRequest' => [
                'merchantAuthentication' => [
                    'name' => $apiCredentials['apiKey'],
                    'transactionKey' => $apiCredentials['transactionKey']
                ],
                'transId' => $transactionId
            ]
        ];

        $this->_ApiLogger->info('[AuthorizeNetApi] GetTransactionDetails Request: ' . $this->_json->serialize($payload));
        $this->logSavedCC('[AuthorizeNetApi] GetTransactionDetails Request: ' . $this->_json->serialize($payload));

        $params = [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => $this->_json->serialize($payload)
        ];

        $response = $this->_doRequest('', Request::HTTP_METHOD_POST, $params);
        return $this->removeByteOrderMarkFromJsonString($response->getBody()->getContents());
    }

    public function testAuthorizeAPI($apiLoginId, $transactionKey)
    {
        $body = '
            {
                "authenticateTestRequest": {
                    "merchantAuthentication": {
                        "name": "' . $apiLoginId . '",
                        "transactionKey": "' . $transactionKey . '"
                    }
                }
            }';

        $params             = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body'          => $body
        ];
        $response           = $this->_doRequest($chargeApiEndpoint = '', Request::HTTP_METHOD_POST, $params);
        $status             = $response->getStatusCode(); // 200 status code
        $responseBody       = $response->getBody();
        $responseContent    = $responseBody->getContents(); // here you will have the API response in JSON format

        $responseContent = $this->removeByteOrderMarkFromJsonString($responseContent);
        return $responseContent;
    }

    public function removeByteOrderMarkFromJsonString($responseContent)
    {
        // Remove the  Byte Order Mark (BOM) from the string
        if (substr($responseContent, 0, 3) === "\xEF\xBB\xBF") {
            $responseContent = substr($responseContent, 3);
        }
        return $responseContent;
    }

    public function getApiDetails()
    {
        $apiDetails = $this->_helper->getApiCredentials();
        return $apiDetails;
    }

    public function getApiCredentials()
    {
        $apiDetails = $this->_helper->getApiCredentials();
        $apiCredentials = [
            'apiKey' => $apiDetails['apiLoginId'],
            'transactionKey' => $this->_helper->decryptValue($apiDetails['transactionKey'])
        ];
        return $apiCredentials;
    }

    public function paymentEnvironment()
    {
        $paymentEnvironment = $this->getApiDetails();
        $this->isSandbox = ($paymentEnvironment['accountType'] == 'sandBoxAccount') ? true : false;
    }


    private function _doRequest(string  $uriEndpoint,  string  $requestMethod,  array   $params = []): Response
    {
        $this->paymentEnvironment();
        if ($this->isSandbox) {
            $apiEndPoint = self::API_REQUEST_URI_SANDBOX;
        } else {
            $apiEndPoint = self::API_REQUEST_URI_LIVE;
        }
        $client = $this->_clientFactory->create([
            'config' => [
                'base_uri' => $apiEndPoint
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
            $this->_ApiLogger->info(' In catch ' . ' <pre> ' . $exception->getMessage() . ' from Ahy\Authorizenet\Service\ApiService ');
            $this->logSavedCC(' In catch ' . ' <pre> ' . $exception->getMessage() . ' from Ahy\Authorizenet\Service\ApiService ');
        }
        return $response;
    }


    public function chargeSavedCard(
        string $customerProfileId,
        string $paymentProfileId,
        float $amount,
        array $shippingAddressArray = [],
        array $billingAddressArray = [],
        array $customerDetailsArray = []
    ): string {
        $apiCredentials = $this->getApiCredentials();

        // Log initial context only for logging - debug
        $this->logger->info('[chargeSavedCard] Start charging saved card', [
            'customerProfileId' => $customerProfileId,
            'paymentProfileId' => $paymentProfileId,
            'amount' => $amount,
            'customerId' => $customerDetailsArray['customerId'] ?? 'N/A',
            'email' => $customerDetailsArray['email'] ?? 'N/A',
        ]);

        // Prepare shipping address
        $shippingAddress = [
            'firstName' => $shippingAddressArray['firstName'] ?? 'N/A',
            'lastName'  => $shippingAddressArray['lastName'] ?? 'N/A',
            'address'   => $shippingAddressArray['street'] ?? 'N/A',
            'city'      => $shippingAddressArray['city'] ?? 'N/A',
            'state'     => $shippingAddressArray['region'] ?? 'N/A',
            'zip'       => $shippingAddressArray['postcode'] ?? '00000',
            'country'   => $shippingAddressArray['country_code'] ?? 'US'
        ];

        // Prepare customer details
        $customer = [
            'id'    => $customerDetailsArray['customerId'] ?? 'guest',
            'email' => $customerDetailsArray['email'] ?? 'guest@example.com'
        ];

        // Prepare API request body
        $payload = [
            'createTransactionRequest' => [
                'merchantAuthentication' => [
                    'name'           => $apiCredentials['apiKey'],
                    'transactionKey' => $apiCredentials['transactionKey'],
                ],
                'transactionRequest' => [
                    'transactionType' => 'authCaptureTransaction',
                    'amount'          => number_format($amount, 2, '.', ''),
                    'profile'         => [
                        'customerProfileId' => $customerProfileId,
                        'paymentProfile'    => [
                            'paymentProfileId' => $paymentProfileId
                        ]
                    ],
                    'customer' => $customer,
                    'shipTo'   => $shippingAddress
                ]
            ]
        ];

        $jsonPayload = $this->_json->serialize($payload);
        $this->logger->info('[chargeSavedCard] Request: ' . $jsonPayload);
        $this->logSavedCC('[chargeSavedCard] Request: ' . $jsonPayload);

        $params = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => $jsonPayload
        ];

        try {
            $response = $this->_doRequest('', Request::HTTP_METHOD_POST, $params);
            $responseContent = $response->getBody()->getContents();
            $responseContent = $this->removeByteOrderMarkFromJsonString($responseContent);
            $this->logger->info('[chargeSavedCard] Response: ' . $responseContent);
            $this->logSavedCC('[chargeSavedCard] Response: ' . $responseContent);
            return $responseContent;
        } catch (GuzzleException $e) {
            $this->logger->error('[chargeSavedCard] Guzzle Exception: ' . $e->getMessage());
            $this->logSavedCC('[chargeSavedCard] Guzzle Exception: ' . $e->getMessage());
            throw new \Exception('Failed to charge saved card.');
        } catch (\Exception $e) {
            $this->logger->error('[chargeSavedCard] General Exception: ' . $e->getMessage());
            $this->logSavedCC('[chargeSavedCard] General Exception: ' . $e->getMessage());
            throw $e;
        }
    }
}
