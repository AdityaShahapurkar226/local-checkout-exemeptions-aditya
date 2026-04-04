<?php

namespace Ahy\Authorizenet\Service;

use GuzzleHttp\Client;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Ahy\Authorizenet\Model\CustomerProfileRepository;
use Magento\Framework\App\State;
use Ahy\Authorizenet\Logger\SavedCCFrontendLogger;
use Ahy\Authorizenet\Logger\SavedCCAdminLogger;

class AuthorizeNetCimService
{
    // Sandbox credentials (use Magento config in production)
    public const API_REQUEST_URI_SANDBOX = 'https://apitest.authorize.net/xml/v1/request.api';
    public const API_LOGIN_KEY           = '8e4y98EcJF7c';
    public const API_TRANSACTION_KEY     = '2XvaHQ5r5u57NM2m';

    protected Client $client;
    protected Json $json;
    protected LoggerInterface $logger;
    protected AuthorizeNetApi $authorizeNetApi;
    protected CustomerProfileRepository $profileRepository;
    protected State $appState;
    protected SavedCCFrontendLogger $savedCCFrontendLogger;
    protected SavedCCAdminLogger $savedCCAdminLogger;

    public function __construct(
        Client $client,
        Json $json,
        LoggerInterface $logger,
        AuthorizeNetApi $authorizeNetApi,
        CustomerProfileRepository $profileRepository,
        State $appState,
        SavedCCFrontendLogger $savedCCFrontendLogger,
        SavedCCAdminLogger $savedCCAdminLogger
    ) {
        $this->client            = $client;
        $this->json              = $json;
        $this->logger            = $logger;
        $this->authorizeNetApi   = $authorizeNetApi;
        $this->profileRepository = $profileRepository;
        $this->appState = $appState;
        $this->savedCCFrontendLogger = $savedCCFrontendLogger;
        $this->savedCCAdminLogger = $savedCCAdminLogger;
    }

    /* =========================================================================
       ==============   CREATE PAYMENT PROFILE (main entry)   ==================
       ========================================================================= */

    /**
     * Creates a new Authorize.Net payment profile.
     * If customerProfileId is missing, creates customer profile first.
     *
     * @param array $data
     * @return array
     */

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

    public function createCustomerPaymentProfile(array $data): array
    {
        $customerProfileId = $this->getCustomerProfileIdByCustomerId((int)$data['customer_id']);

        // ---------------- CASE 2: No profile found ----------------
        if (!$customerProfileId) {
            $this->logger->info('[CIM] No customerProfileId found – creating profile first.');
            $this->logSavedCC('[CIM] No customerProfileId found – creating profile first.');

            $createProfileResp = $this->authorizeNetApi->createCustomerProfile(array_merge(
                [
                    'merchant_customer_id' => (string)$data['customer_id'],
                    'email'                => $data['email'] ?? '',
                    'validation_mode'      => $data['validation_mode'] ?? 'testMode',
                ],
                $data
            ));

            if (!$createProfileResp['success']) {
                $this->logSavedCC('[SavedCC] Failed to create customer profile', ['error' => $createProfileResp['message']]);
                return ['success' => false, 'message' => $createProfileResp['message']];
            }

            $customerProfileId = $createProfileResp['customer_profile_id'];
            $paymentProfileId  = $createProfileResp['payment_profile_id'];

            try {
                $this->profileRepository->saveProfileMapping((int)$data['customer_id'], $customerProfileId);
                $this->logger->info("[CIM] Saved new customerProfileId {$customerProfileId} for customer {$data['customer_id']}");
                $this->logSavedCC('[SavedCC] Saved new customerProfileId', [
                    'customer_id' => $data['customer_id'],
                    'profile_id' => $customerProfileId
                ]);
            } catch (\Exception $e) {
                $this->logger->error('[CIM] Failed to save profile mapping: ' . $e->getMessage());
                $this->logSavedCC('[SavedCC] Failed to save profile mapping', ['exception' => $e->getMessage()]);
                // Continue; do not fail card save
            }

            return [
                'success'             => true,
                'payment_profile_id'  => $paymentProfileId,
                'customer_profile_id' => $customerProfileId,
            ];
        }

        // ---------------- CASE 1: Profile exists ----------------

        $payload = [
            'createCustomerPaymentProfileRequest' => [
                'merchantAuthentication' => [
                    'name'           => self::API_LOGIN_KEY,
                    'transactionKey' => self::API_TRANSACTION_KEY,
                ],
                'customerProfileId' => $customerProfileId,
                'paymentProfile'    => [
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
                    'defaultPaymentProfile' => false,
                ],
                'validationMode' => $data['validation_mode'] ?? 'testMode',
            ],
        ];

        $jsonPayload = $this->json->serialize($payload);
        $this->logger->info('[CIM] Payload for createCustomerPaymentProfileRequest: ' . $jsonPayload);
        $this->logSavedCC('[CIM] Payload for createCustomerPaymentProfileRequest: ' . $jsonPayload);

        try {
            $response = $this->client->post(self::API_REQUEST_URI_SANDBOX, [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => $jsonPayload,
            ]);

            $decoded = $this->json->unserialize(
                trim($response->getBody()->getContents(), "\xEF\xBB\xBF\x00..\x1F")
            );

            if (
                !isset($decoded['messages']['resultCode']) ||
                $decoded['messages']['resultCode'] !== 'Ok'
            ) {
                $messages = $decoded['messages']['message'] ?? [];
                foreach ($messages as $error) {
                    $code = $error['code'] ?? '';
                    $text = $error['text'] ?? 'Unknown error';

                    $this->logger->error("[CIM] Error [$code]: $text");
                    $this->logSavedCC('[CIM] Error response', ['code' => $code, 'text' => $text]);

                    if ($code === 'E00042' || stripos($text, 'maximum number') !== false) {
                        return [
                            'success' => false,
                            'message' => __('You have reached the maximum number of saved cards (10). Please delete an existing card to add a new one.')
                        ];
                    }
                }

                $defaultMsg = $messages[0]['text'] ?? 'Unknown error occurred.';
                return ['success' => false, 'message' => $defaultMsg];
            }

            $paymentProfileId = $decoded['customerPaymentProfileId'];
            $this->logger->info('[CIM] createCustomerPaymentProfile success ID: ' . $paymentProfileId);
            $this->logSavedCC('[SavedCC] Created payment profile successfully', [
                'payment_profile_id' => $paymentProfileId,
                'customer_id' => $data['customer_id']
            ]);
            return [
                'success'            => true,
                'payment_profile_id' => $paymentProfileId,
            ];
        } catch (\Exception $e) {
            $this->logger->error('[CIM] Error in createCustomerPaymentProfile: ' . $e->getMessage());
            $this->logSavedCC('[CIM] Error in createCustomerPaymentProfile: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /* =========================================================================
       ===================  UPDATE PAYMENT PROFILE ============================
       ========================================================================= */

    public function updateCustomerPaymentProfile(array $data): void
    {
        $payload = [
            'updateCustomerPaymentProfileRequest' => [
                'merchantAuthentication' => [
                    'name'           => self::API_LOGIN_KEY,
                    'transactionKey' => self::API_TRANSACTION_KEY,
                ],
                'customerProfileId' => $data['customerProfileId'],
                'paymentProfile'    => [
                    'billTo' => [
                        'firstName'   => $data['billing_first_name'] ?? '',
                        'lastName'    => $data['billing_last_name'] ?? '',
                        'company'     => '',
                        'address'     => $data['billing_street'] ?? '',
                        'city'        => $data['billing_city'] ?? '',
                        'state'       => $data['billing_state'] ?? '',
                        'zip'         => $data['billing_zip'] ?? '',
                        'country'     => $data['billing_country'] ?? '',
                        'phoneNumber' => $data['billing_phone'] ?? '',
                        'faxNumber'   => '',
                    ],
                    'payment' => [
                        'creditCard' => [
                            'cardNumber'     => $data['cardNumber'] ?? '',
                            'expirationDate' => $data['expirationDate'] ?? '',
                            'cardCode'       => $data['cvv'] ?? '',
                        ],
                    ],
                    'defaultPaymentProfile' => false,
                    'customerPaymentProfileId' => $data['paymentProfileId'],
                ],
                'validationMode' => $data['validation_mode'] ?? 'testMode',
            ],
        ];

        $jsonPayload = $this->json->serialize($payload);
        $this->logger->info('[AuthorizeNetCIM] Payload for updateCustomerPaymentProfileRequest: ' . $jsonPayload);
        $this->logSavedCC('[AuthorizeNetCIM] Payload for updateCustomerPaymentProfileRequest: ' . $jsonPayload);

        try {
            $response = $this->client->post(self::API_REQUEST_URI_SANDBOX, [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => $jsonPayload,
            ]);

            $body = trim($response->getBody()->getContents(), "\xEF\xBB\xBF\x00..\x1F");
            $decoded = $this->json->unserialize($body);

            if (
                !isset($decoded['messages']['resultCode']) ||
                $decoded['messages']['resultCode'] !== 'Ok'
            ) {
                $message = $decoded['messages']['message'][0]['text'] ?? 'Unknown error.';
                throw new \Exception('Authorize.Net update failed: ' . $message);
            }

            $this->logger->info('[AuthorizeNetCIM] Update success response: ', $decoded);
            $this->logSavedCC('[AuthorizeNetCIM] Update success response: ', $decoded);
        } catch (\Exception $e) {
            $this->logger->error('[CIM] API error during updateCustomerPaymentProfile: ' . $e->getMessage());
            $this->logSavedCC('[CIM] API error during updateCustomerPaymentProfile: ' . $e->getMessage());
            throw $e;
        }
    }

    /* =========================================================================
       ===================  DELETE PAYMENT PROFILE ============================
       ========================================================================= */

    public function deleteCustomerPaymentProfile(string $customerProfileId, string $paymentProfileId): void
    {
        $payload = [
            'deleteCustomerPaymentProfileRequest' => [
                'merchantAuthentication' => [
                    'name'           => self::API_LOGIN_KEY,
                    'transactionKey' => self::API_TRANSACTION_KEY,
                ],
                'customerProfileId'        => $customerProfileId,
                'customerPaymentProfileId' => $paymentProfileId,
            ],
        ];

        $jsonPayload = $this->json->serialize($payload);
        $this->logger->info('[AuthorizeNetCIM] Payload for deleteCustomerPaymentProfileRequest: ' . $jsonPayload);
        $this->logSavedCC('[AuthorizeNetCIM] Payload for deleteCustomerPaymentProfileRequest: ' . $jsonPayload);

        try {
            $response = $this->client->post(self::API_REQUEST_URI_SANDBOX, [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => $jsonPayload,
            ]);

            $body = trim($response->getBody()->getContents(), "\xEF\xBB\xBF\x00..\x1F");
            $decoded = $this->json->unserialize($body);

            if ($decoded['messages']['resultCode'] !== 'Ok') {
                $msg = $decoded['messages']['message'][0]['text'] ?? 'Unknown error.';
                $this->logger->error('[AuthorizeNetCIM] Full error response: ' . print_r($decoded, true));
                $this->logSavedCC('[AuthorizeNetCIM] Full error response: ' . print_r($decoded, true));
                throw new \Exception('Authorize.Net delete failed: ' . $msg);
            }

            $this->logger->info("[AuthorizeNetCIM] Deleted payment profile ID: {$paymentProfileId}");
            $this->logSavedCC("[AuthorizeNetCIM] Deleted payment profile ID: {$paymentProfileId}");
        } catch (\Exception $e) {
            $this->logger->error('[CIM] Error deleting card: ' . $e->getMessage());
            $this->logSavedCC('[CIM] Error deleting card: ' . $e->getMessage());
            throw $e;
        }
    }

    /* =========================================================================
       ===================  HELPER FUNCTIONS  =================================
       ========================================================================= */

    public function getCustomerProfileIdByCustomerId(int $customerId): ?string
    {
        try {
            return $this->profileRepository->getProfileIdByCustomerId($customerId);
        } catch (\Exception $e) {
            $this->logger->error('[AuthorizeNetCIM] Failed to get customerProfileId: ' . $e->getMessage());
            $this->logSavedCC('[AuthorizeNetCIM] Failed to get customerProfileId: ' . $e->getMessage());
            return null;
        }
    }
}
