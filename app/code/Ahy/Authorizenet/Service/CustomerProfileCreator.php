<?php

declare(strict_types=1);

namespace Ahy\Authorizenet\Service;

use Psr\Log\LoggerInterface;
use Magento\Vault\Model\PaymentTokenFactory;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Ahy\Authorizenet\Model\CustomerProfileRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Ahy\Authorizenet\Service\AuthorizeNetCimService;
use Magento\Framework\Session\SessionManagerInterface;
use Ahy\SavedCC\Helper\Config as SavedCCConfig;
use Magento\Checkout\Model\Session as CheckoutSession;
use Ahy\Authorizenet\Helper\Data;

class CustomerProfileCreator
{
    /** Core services */
    protected AuthorizeNetApi $authorizeNetApi;
    protected LoggerInterface $logger;

    /** Vault services */
    protected PaymentTokenFactory $paymentTokenFactory;
    protected PaymentTokenRepositoryInterface $paymentTokenRepository;
    protected SearchCriteriaBuilder $searchCriteriaBuilder;
    protected AuthorizeNetCimService $cimService;

    /** Misc */
    protected EncryptorInterface $encryptor;
    protected CustomerProfileRepository $profileRepository;
    protected SessionManagerInterface $session;
    protected SavedCCConfig $configHelper;
    protected CheckoutSession $checkoutSession;
    protected Data $helperData;

    /* ---------- constructor ------------------------------------------------ */

    public function __construct(
        AuthorizeNetApi $authorizeNetApi,
        LoggerInterface $logger,
        PaymentTokenFactory $paymentTokenFactory,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        EncryptorInterface $encryptor,
        CustomerProfileRepository $profileRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AuthorizeNetCimService $cimService,
        SessionManagerInterface $session,
        SavedCCConfig $configHelper,
        CheckoutSession $checkoutSession,
        Data $helperData
    ) {
        $this->authorizeNetApi        = $authorizeNetApi;
        $this->logger                 = $logger;
        $this->paymentTokenFactory    = $paymentTokenFactory;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->encryptor              = $encryptor;
        $this->profileRepository      = $profileRepository;
        $this->searchCriteriaBuilder  = $searchCriteriaBuilder;
        $this->cimService             = $cimService;
        $this->session                = $session;
        $this->configHelper           = $configHelper;
        $this->checkoutSession        = $checkoutSession;
        $this->helperData             = $helperData;
    }

    /* ---------- main entry‑point ------------------------------------------ */

    public function createFromTransaction(
        string $transactionId,
        string $customerEmail,
        string $merchantCustomerId,
        int    $customerId,
        string $last4,
        string $expiryMonth,
        string $expiryYear,
        string $cardType
    ): ?array {
        try {
            // Skip entire process if user is a guest user
            if ($customerId <= 0) {
                $this->logger->info("[CustomerProfileCreator] Guest checkout (customer_id = 0) — skipping customer profile creation and vault storage.");
                return null;
            }

            // Skip if Saved CC feature is disabled in admin
            if (!$this->configHelper->isEnabled()) {
                $this->logger->info("[CustomerProfileCreator] SavedCC feature disabled in config — skipping customer profile creation and vault storage.");
                return null;
            }

            // ============================================================
            // NEW: Check if the "Save Card" checkbox was checked
            // ============================================================
            $shouldSaveCard = $this->shouldSaveCard();
            
            if (!$shouldSaveCard) {
                $this->logger->info("[CustomerProfileCreator] 'Save Card' checkbox not checked — skipping customer profile creation and vault storage.");
                // Clean up session data
                $this->cleanupSessionData();
                return null;
            }

            $this->logger->info("Creating Customer Profile from Transaction ID: {$transactionId}");

            $existingProfileId = $this->profileRepository->getProfileIdByCustomerId($customerId);

            if ($existingProfileId) {
                $this->logger->info("Existing customerProfileId found: {$existingProfileId}");

                // Step 1: Get transaction details
                $transactionDetailsJson = $this->authorizeNetApi->getTransactionDetails($transactionId);
                $transactionDetails = json_decode($transactionDetailsJson, true);

                // Step 2: Extract billing/card details
                $payload = $this->buildPaymentProfilePayloadFromTransaction($transactionDetails);

                // Step 3: Override card data with actual values stored in session
                $realCardNumber = $this->session->getData('authnet_card_number');
                $realExpiryDate = $this->session->getData('authnet_expiration_date'); // Format: YYYY-MM

                if ($realCardNumber && $realExpiryDate) {
                    $payload['payment']['creditCard']['cardNumber'] = $realCardNumber;
                    $payload['payment']['creditCard']['expirationDate'] = $realExpiryDate;
                } else {
                    $this->logger->warning('[CustomerProfileCreator] Real card data missing from session.');
                }

                // Step 4: Create customer payment profile
                $response = $this->authorizeNetApi->createCustomerPaymentProfile($existingProfileId, $payload);
                $this->logger->info('[CustomerProfileCreator] Response from createCustomerPaymentProfile: ' . json_encode($response));

                if (isset($response['customerPaymentProfileId'])) {
                    $gatewayToken = $response['customerPaymentProfileId'];

                    // Save to vault
                    $this->saveVaultToken(
                        $customerId,
                        'authnetahypayment',
                        $gatewayToken,
                        $last4,
                        $expiryMonth,
                        $expiryYear,
                        $cardType
                    );

                    // Clear sensitive session data
                    $this->cleanupSessionData();

                    return [
                        'customerProfileId'        => $existingProfileId,
                        'customerPaymentProfileId' => $gatewayToken
                    ];
                }

                $this->logger->error('Failed to create customer payment profile', $response);
            } else {
                // fallback to original method
                $responseJson = $this->authorizeNetApi->createCustomerProfileFromTransaction(
                    $transactionId,
                    $customerEmail,
                    $merchantCustomerId
                );
                $response = json_decode($responseJson, true);

                if (
                    isset($response['messages']['resultCode']) &&
                    $response['messages']['resultCode'] === 'Ok'
                ) {
                    $this->logger->info('Customer Profile Created Successfully', $response);

                    $customerProfileId = $response['customerProfileId'] ?? null;
                    $paymentProfileIds = $response['customerPaymentProfileIdList'] ?? [];

                    if ($customerProfileId && !$this->profileRepository->hasProfile($customerId)) {
                        $this->profileRepository->saveProfileMapping($customerId, $customerProfileId);
                    }

                    if (!empty($paymentProfileIds)) {
                        $gatewayToken = $paymentProfileIds[0];
                        $this->saveVaultToken(
                            $customerId,
                            'authnetahypayment',
                            $gatewayToken,
                            $last4,
                            $expiryMonth,
                            $expiryYear,
                            $cardType
                        );
                    }

                    // Clear sensitive session data
                    $this->cleanupSessionData();

                    return $response;
                }

                $this->logger->error('Failed to create customer profile', $response);
            }
        } catch (\Exception $e) {
            $this->logger->error('Exception while creating customer profile: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Check if the "Save Card" checkbox was checked during checkout
     */
    protected function shouldSaveCard(): bool
    {
        try {
            // Get the encrypted checkbox state from session
            $saveCardEncrypted = $this->checkoutSession->getData('saveCardCheckbox');
            
            if (empty($saveCardEncrypted)) {
                $this->logger->info("[CustomerProfileCreator] No checkbox data found in session");
                return false;
            }

            // Decrypt the checkbox value
            $encryptionKey = $this->helperData->getEncryptionKey();
            $saveCard = $this->decryptSession($saveCardEncrypted, $encryptionKey);
            
            $this->logger->info("[CustomerProfileCreator] Checkbox value decrypted: " . var_export($saveCard, true));

            // Check if it's true
            return ($saveCard === 'true' || $saveCard === true || $saveCard === '1');
            
        } catch (\Exception $e) {
            $this->logger->error("[CustomerProfileCreator] Error checking checkbox state: " . $e->getMessage());
            return false; // If error, don't save card
        }
    }

    /**
     * Decrypt session data
     */
    protected function decryptSession($encrypted, $encryptionKey): ?string
    {
        try {
            $method = 'aes-256-cbc';
            $key = substr(hash('sha256', $encryptionKey), 0, 32);
            $decoded = base64_decode($encrypted);
            $iv = substr($decoded, 0, 16);
            $data = substr($decoded, 16);
            return openssl_decrypt($data, $method, $key, OPENSSL_RAW_DATA, $iv);
        } catch (\Exception $e) {
            $this->logger->error('[CustomerProfileCreator] Decryption error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Clean up sensitive session data after processing
     */
    protected function cleanupSessionData(): void
    {
        try {
            $this->session->unsetData('authnet_card_number');
            $this->session->unsetData('authnet_expiration_date');
            $this->checkoutSession->unsetData('saveCardCheckbox');
            $this->logger->info("[CustomerProfileCreator] Session data cleaned up");
        } catch (\Exception $e) {
            $this->logger->error('[CustomerProfileCreator] Error cleaning session: ' . $e->getMessage());
        }
    }

    protected function buildPaymentProfilePayloadFromTransaction(array $transaction): array
    {
        $billTo = $transaction['transaction']['billTo'] ?? [];
        $payment = $transaction['transaction']['payment']['creditCard'] ?? [];

        return [
            'billTo' => [
                'firstName'   => $billTo['firstName'] ?? '',
                'lastName'    => $billTo['lastName'] ?? '',
                'address'     => $billTo['address'] ?? '',
                'city'        => $billTo['city'] ?? '',
                'state'       => $billTo['state'] ?? '',
                'zip'         => $billTo['zip'] ?? '',
                'country'     => $billTo['country'] ?? '',
                'phoneNumber' => $billTo['phoneNumber'] ?? '',
            ],
            'payment' => [
                'creditCard' => [
                    'cardNumber'     => $payment['cardNumber'] ?? '',
                    'expirationDate' => $payment['expirationDate'] ?? '',
                ],
            ],
        ];
    }

    /* ---------- vault helpers -------------------------------------------- */

    /**
     * Save a new payment token to the Vault unless a duplicate card already exists.
     */
    protected function saveVaultToken(
        int    $customerId,
        string $paymentMethodCode,
        string $gatewayToken,
        string $last4,
        string $expiryMonth,
        string $expiryYear,
        string $cardType
    ): void {
        /* Deduplication */
        $cardType = $this->normalizeCardType($cardType);
        if ($this->isDuplicateCard($customerId, $last4, $expiryMonth, $expiryYear, $cardType)) {
            $this->logger->info("Duplicate card detected – skipping Vault save for customer {$customerId}");
            return;
        }

        /* Create & populate token */
        $token = $this->paymentTokenFactory->create();
        $token->setCustomerId($customerId);
        $token->setPaymentMethodCode($paymentMethodCode);
        $token->setGatewayToken($gatewayToken);
        $token->setIsActive(true);
        $token->setIsVisible(true);
        $token->setType('card');

        $token->setExpiresAt($this->getExpirationTimestamp($expiryMonth, $expiryYear));
        $token->setPublicHash(hash('sha256', $paymentMethodCode . $gatewayToken . $customerId));

        $token->setTokenDetails(json_encode([
            'type'            => strtoupper($cardType),
            'maskedCC'        => $last4,
            'expirationDate'  => sprintf('%02d/%s', $expiryMonth, $expiryYear)
        ], JSON_UNESCAPED_SLASHES));

        /* Persist */
        try {
            $this->paymentTokenRepository->save($token);
            $this->logger->info("Vault token saved for customer ID: {$customerId}");
        } catch (\Exception $e) {
            $this->logger->error('Error saving vault token: ' . $e->getMessage());
        }
    }

    /**
     * Check if the same card (last4 + expiry + type) already exists in the Vault for this customer.
     */
    protected function isDuplicateCard(
        int    $customerId,
        string $last4,
        string $expiryMonth,
        string $expiryYear,
        string $cardType
    ): bool {
        $criteria = $this->searchCriteriaBuilder
            ->addFilter('customer_id', $customerId)
            ->addFilter('is_active', 1)
            ->create();

        $existingTokens = $this->paymentTokenRepository->getList($criteria)->getItems();

        foreach ($existingTokens as $token) {
            /** @var PaymentTokenInterface $token */
            $details = json_decode($token->getTokenDetails() ?? '[]', true);
            if (!$details) {
                continue;
            }

            $isSameCard = (
                ($details['maskedCC']        ?? '') === $last4 &&
                strtoupper($details['type']  ?? '') === strtoupper($cardType) &&
                ($details['expirationDate']  ?? '') === sprintf('%02d/%s', $expiryMonth, $expiryYear)
            );

            if ($isSameCard) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a timestamp of 23:59:59 on the last day of the card's expiry month.
     */
    protected function getExpirationTimestamp(string $month, string $year): string
    {
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', "{$year}-{$month}-01 00:00:00");
        $date->modify('+1 month')->modify('-1 second');   // end of month
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Map verbose card type names to their short codes.
     */

    protected function normalizeCardType(string $type): string

    {
        $map = [
            'AMERICANEXPRESS' => 'AMEX',
            'AMERICAN EXPRESS' => 'AMEX',
            'VISA' => 'VISA',
            'MASTERCARD' => 'MASTERCARD',
            'DISCOVER' => 'DISCOVER',
        ];
        $upper = strtoupper(trim($type));
        return $map[$upper] ?? $upper;
    }
}