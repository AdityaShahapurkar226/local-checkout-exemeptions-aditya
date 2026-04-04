<?php

namespace Ahy\Authorizenet\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Vault\Model\PaymentTokenFactory;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Ahy\Authorizenet\Service\AuthorizeNetCimService;
use Magento\Customer\Api\AddressRepositoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Ahy\Authorizenet\Helper\Decryptor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class CreateSavedCard extends Action
{
    protected $customerSession;
    protected $resultRedirectFactory;
    protected $messageManager;
    protected $paymentTokenFactory;
    protected $paymentTokenRepository;
    protected $encryptor;
    protected $dateTime;
    protected $cimService;
    protected $addressRepository;
    protected $logger;
    protected $formKeyValidator;
    protected $searchCriteriaBuilder;
    protected $decryptor;
    protected $scopeConfig;

    public function __construct(
        Context $context,
        Session $customerSession,
        RedirectFactory $resultRedirectFactory,
        ManagerInterface $messageManager,
        PaymentTokenFactory $paymentTokenFactory,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        EncryptorInterface $encryptor,
        DateTime $dateTime,
        AuthorizeNetCimService $cimService,
        AddressRepositoryInterface $addressRepository,
        LoggerInterface $logger,
        FormKeyValidator $formKeyValidator,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Decryptor $decryptor,
        ScopeConfigInterface $scopeConfig,

    ) {
        parent::__construct($context);

        $this->customerSession         = $customerSession;
        $this->resultRedirectFactory   = $resultRedirectFactory;
        $this->messageManager          = $messageManager;
        $this->paymentTokenFactory     = $paymentTokenFactory;
        $this->paymentTokenRepository  = $paymentTokenRepository;
        $this->encryptor               = $encryptor;
        $this->dateTime                = $dateTime;
        $this->cimService              = $cimService;
        $this->addressRepository       = $addressRepository;
        $this->logger                  = $logger;
        $this->formKeyValidator        = $formKeyValidator;
        $this->searchCriteriaBuilder   = $searchCriteriaBuilder;
        $this->decryptor               = $decryptor;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        $validationMode = $this->scopeConfig->getValue(
            'payment/ahy_savedcc/validation_mode',
            ScopeInterface::SCOPE_STORE
        );

        $redirect = $this->resultRedirectFactory->create()->setPath('customer/account');

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(__('Invalid form submission.'));
            return $redirect;
        }
        // $saveCard = (bool) ($this->getRequest()->getParam('save_card') ?? false);
        // $this->logger->info('[CreateSavedCard] Save card parameter: ' . ($saveCard ? 'true' : 'false'));

        // if (!$saveCard) {
        //     $this->logger->info('[CreateSavedCard] Save card aborted by user choice');
        //     $this->messageManager->addNoticeMessage(__('Card was not saved.'));
        //     return $redirect;
        // }


        $customerId = (int) $this->customerSession->getCustomerId();

        if (!$customerId) {
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }

        $params = $this->getRequest()->getParams();
        $secretKey = 'my_static_secret_key';

        // Card number
        $cardNumberEnc = $params['card_number'] ?? '';
        $cardNumber = $cardNumberEnc ? $this->decryptor->decrypt($cardNumberEnc, $secretKey) : '';

        // CVV
        $cvvEnc = $params['cvv'] ?? '';
        $cvv = $cvvEnc ? $this->decryptor->decrypt($cvvEnc, $secretKey) : '';

        // Expiration month & year
        $expirationMonthEnc = $params['expiration_month'] ?? '';
        $expirationYearEnc  = $params['expiration_year'] ?? '';

        $expirationMonth = $expirationMonthEnc ? $this->decryptor->decrypt($expirationMonthEnc, $secretKey) : '';
        $expirationYear  = $expirationYearEnc ? $this->decryptor->decrypt($expirationYearEnc, $secretKey) : '';



        if (!$cardNumber || !$expirationMonth || !$expirationYear || !$cvv) {
            $this->messageManager->addErrorMessage(__('Please fill all required credit card fields.'));
            return $redirect;
        }

        $billingAddressId = (int) ($params['billing_address_id'] ?? 0);

        if (!$billingAddressId) {
            $this->messageManager->addErrorMessage(__('Please select a billing address.'));
            return $redirect;
        }

        try {
            $billing = $this->addressRepository->getById($billingAddressId);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Invalid billing address selected.'));
            return $redirect;
        }

        $expirationDate      = sprintf('%s-%s', $expirationYear, str_pad($expirationMonth, 2, '0', STR_PAD_LEFT));
        $maskedCC            = substr($cardNumber, -4);
        $cardType            = $this->detectCardType($cardNumber);
        $expirationFormatted = $expirationMonth . '/' . $expirationYear;

        // Check for duplicate card
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('customer_id', $customerId)
            ->addFilter('payment_method_code', 'authnetahypayment')
            ->addFilter('is_active', true)
            ->create();

        $existingTokens = $this->paymentTokenRepository->getList($searchCriteria)->getItems();

        foreach ($existingTokens as $token) {
            $details = json_decode($token->getTokenDetails(), true);

            if (
                isset($details['maskedCC'], $details['type'], $details['expirationDate']) &&
                $details['maskedCC'] === $maskedCC &&
                $details['type'] === $cardType &&
                $details['expirationDate'] === $expirationFormatted
            ) {
                $this->messageManager->addNoticeMessage(__('This card already exists in your saved cards.'));
                return $redirect;
            }
        }

        // Prepare billing info
        $billTo = [
            'billing_first_name' => $billing->getFirstname(),
            'billing_last_name'  => $billing->getLastname(),
            'billing_street'     => implode(' ', $billing->getStreet()),
            'billing_city'       => $billing->getCity(),
            'billing_state'      => $billing->getRegion() ? $billing->getRegion()->getRegionCode() : '',
            'billing_zip'        => $billing->getPostcode(),
            'billing_country'    => $billing->getCountryId(),
            'billing_phone'      => $billing->getTelephone(),
        ];

        $this->logger->info('[CreateSavedCard] billTo payload: ' . json_encode($billTo));

        try {
            $response = $this->cimService->createCustomerPaymentProfile(array_merge([
                'customer_id'     => $customerId,
                'card_number'     => $cardNumber,
                'expiration_date' => $expirationDate,
                'cvv'             => $cvv,
                'validation_mode' => $validationMode,
            ], $billTo));

            if (empty($response['success']) || empty($response['payment_profile_id'])) {
                throw new \Exception($response['message'] ?? 'Card creation failed.');
            }

            $paymentToken = $this->paymentTokenFactory->create();
            $paymentToken->setCustomerId($customerId);
            $paymentToken->setGatewayToken($response['payment_profile_id']);
            $paymentToken->setPaymentMethodCode('authnetahypayment');
            $paymentToken->setType('card');
            $paymentToken->setIsActive(true);
            $paymentToken->setIsVisible(true);
            $paymentToken->setCreatedAt($this->dateTime->gmtDate());

            // Calculate token expiration in UTC
            try {
                $expirationYear  = (int) $expirationYear;
                $expirationMonth = (int) $expirationMonth;

                // Create first day of the month
                $date = new \DateTimeImmutable(sprintf('%04d-%02d-01', $expirationYear, $expirationMonth), new \DateTimeZone('UTC'));
                // Move to last day of month and set time to 23:59:59
                $date = $date->modify('last day of this month')->setTime(23, 59, 59);

                $expiresAt = $date->format('Y-m-d H:i:s');

                $this->logger->info('[CreateSavedCard] ExpiresAt calculated as: ' . $expiresAt);
            } catch (\Exception $e) {
                throw new \Exception('Invalid expiration date: ' . $expirationFormatted);
            }

            if (!$expiresAt || $expiresAt === '0000-00-00 00:00:00') {
                throw new \Exception('Token expiration time is invalid.');
            }

            $paymentToken->setExpiresAt($expiresAt);

            $details = [
                'type'           => $cardType,
                'maskedCC'       => $maskedCC,
                'expirationDate' => $expirationFormatted,
            ];

            $paymentToken->setTokenDetails(json_encode($details, JSON_UNESCAPED_SLASHES));

            $hashKey = implode('|', [
                $customerId,
                $paymentToken->getGatewayToken(),
                $paymentToken->getPaymentMethodCode(),
                $paymentToken->getType(),
                $paymentToken->getExpiresAt(),
            ]);

            $paymentToken->setPublicHash($this->encryptor->hash($hashKey));
            $this->paymentTokenRepository->save($paymentToken);

            $this->messageManager->addSuccessMessage(__('Card saved successfully.'));
        } catch (\Exception $e) {
            $this->logger->error('[CreateSavedCard] Error: ' . $e->getMessage());
            $this->messageManager->addErrorMessage(__('Failed to save card: ') . $e->getMessage());
        }

        return $redirect;
    }

    private function detectCardType(string $number): string
    {
        $patterns = [
            'VISA'       => '/^4[0-9]{12}(?:[0-9]{3})?$/',
            'MASTERCARD' => '/^(5[1-5][0-9]{14}|2[2-7][0-9]{14})$/',
            'AMEX'       => '/^3[47][0-9]{13}$/',
            'DISCOVER' => '/^(6011[0-9]{12}|65[0-9]{14}|64[4-9][0-9]{13}|622[1-9][0-9]{12,13})$/',
            'JCB'        => '/^(?:2131|1800|35\d{3})\d{11}$/',
            'DINERS'     => '/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/',
        ];

        foreach ($patterns as $type => $regex) {
            if (preg_match($regex, $number)) {
                return $type;
            }
        }

        return 'UNKNOWN';
    }
}
