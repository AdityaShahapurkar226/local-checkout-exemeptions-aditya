<?php

namespace Webkul\Marketplace\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Vault\Model\PaymentTokenFactory;
use Ahy\Authorizenet\Service\AuthorizeNetCimService;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class CreateVaultToken extends Action
{
    protected $cimService;
    protected $resource;
    protected $paymentTokenFactory;
    protected $tokenRepository;
    protected $encryptor;
    protected $json;
    protected $customerRepository;
    protected $searchCriteriaBuilder;
    protected $scopeConfig;


    public function __construct(
        Action\Context $context,
        AuthorizeNetCimService $cimService,
        ResourceConnection $resource,
        PaymentTokenFactory $paymentTokenFactory,
        PaymentTokenRepositoryInterface $tokenRepository,
        EncryptorInterface $encryptor,
        JsonHelper $json,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->cimService = $cimService;
        $this->resource = $resource;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->tokenRepository = $tokenRepository;
        $this->encryptor = $encryptor;
        $this->json = $json;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $data['card_number'] = preg_replace('/\D/', '', $data['card_number']);
        $customerId = (int) $data['customer_id'];

        try {
            $customer = $this->customerRepository->getById($customerId);
            $validationMode = $this->scopeConfig->getValue(
                            'payment/ahy_savedcc/validation_mode',
                            ScopeInterface::SCOPE_STORE
                        );

            $cardData = [
                'customer_id'        => $customerId,
                'email'              => $customer->getEmail(),
                'card_number'        => $data['card_number'],
                'expiration_date'    => sprintf('%s-%s', $data['exp_year'], $data['exp_month']),
                'cvv'                => $data['cvv'],
                'billing_first_name' => $data['billing_first_name'] ?? '',
                'billing_last_name'  => $data['billing_last_name'] ?? '',
                'billing_company'    => $data['billing_company'] ?? '',
                'billing_street'     => $data['billing_street'] ?? '',
                'billing_city'       => $data['billing_city'] ?? '',
                'billing_state'      => $data['billing_state'] ?? '',
                'billing_zip'        => $data['billing_zip'] ?? '',
                'billing_country'    => $data['billing_country'] ?? '',
                'billing_phone'      => $data['billing_phone'] ?? '',
                'billing_fax'        => $data['billing_fax'] ?? '',
                'validation_mode'    => $validationMode,
            ];

            $result = $this->cimService->createCustomerPaymentProfile($cardData);

            if (!$result['success']) {
                throw new \Exception($result['message']);
            }

            $paymentProfileId = $result['payment_profile_id'];
            $expDate = $cardData['expiration_date']; // YYYY-MM
            [$expYear, $expMonth] = explode('-', $expDate);
            $formattedExpDate = sprintf('%s/%s', $expMonth, $expYear);
            $masked = substr($data['card_number'], -4);
            $cardType = $this->detectCardType($data['card_number']);

            // Check for existing token with same last4, expiry, and type
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('customer_id', $customerId)
                ->addFilter('type', 'card')
                ->addFilter('is_active', 1)
                ->create();

            $existingTokens = $this->tokenRepository->getList($searchCriteria)->getItems();

            foreach ($existingTokens as $tokenItem) {
                $details = json_decode($tokenItem->getTokenDetails(), true);
                if (
                    isset($details['maskedCC'], $details['expirationDate'], $details['type']) &&
                    $details['maskedCC'] === $masked &&
                    $details['expirationDate'] === $formattedExpDate &&
                    strtolower($details['type']) === strtolower($cardType)
                ) {
                    // Duplicate found
                    $this->messageManager->addNoticeMessage(__('This card is already saved.'));
                    return $this->_redirect('customer/index/edit', [
                        'id' => $customerId,
                        'active_tab' => 'marketplace_customer_edit_tab_paymentinfo'
                    ]);
                }
            }

            // No duplicate, proceed to save
            $token = $this->paymentTokenFactory->create();
            $token->setCustomerId($customerId);
            $token->setGatewayToken($paymentProfileId);
            $token->setPaymentMethodCode('authnetahypayment');
            $token->setType('card');
            $token->setCreatedAt(date('Y-m-d H:i:s'));
            $token->setExpiresAt($this->getExpirationTimestamp($expMonth, $expYear));
            $token->setIsActive(1);
            $token->setIsVisible(1);

            $details = [
                'type'           => $cardType,
                'maskedCC'       => $masked,
                'expirationDate' => $formattedExpDate,
            ];
            $token->setTokenDetails(json_encode($details, JSON_UNESCAPED_SLASHES));
            $token->setPublicHash(hash('sha256', $paymentProfileId . $customerId));

            $this->tokenRepository->save($token);
            $this->messageManager->addSuccessMessage(__('Card saved successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Failed to save card: %1', $e->getMessage()));
        }

        return $this->_redirect('customer/index/edit', [
            'id' => $customerId,
            'active_tab' => 'marketplace_customer_edit_tab_paymentinfo'
        ]);
    }

    private function getExpirationTimestamp($month, $year): string
    {
        $expirationDate = sprintf('%s-%s', $year, str_pad($month, 2, '0', STR_PAD_LEFT));
        return date('Y-m-t 23:59:59', strtotime($expirationDate));
    }

    /**
     * Detects card brand from card number
     */
    private function detectCardType(string $cardNumber): string
    {
        $cardNumber = preg_replace('/\D/', '', $cardNumber);

        $patterns = [
            'VISA' => '/^4[0-9]{12}(?:[0-9]{3})?$/',
            'MASTERCARD' => '/^(5[1-5][0-9]{14}|2[2-7][0-9]{14})$/',
            'AMEX' => '/^3[47][0-9]{13}$/',
            'DISCOVER' => '/^(6011[0-9]{12}|65[0-9]{14}|64[4-9][0-9]{13}|622[1-9][0-9]{12,13})$/',
            'JCB' => '/^(?:2131|1800|35\d{3})\d{11}$/',
            'DINERS' => '/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/',
        ];

        foreach ($patterns as $type => $regex) {
            if (preg_match($regex, $cardNumber)) {
                return $type;
            }
        }

        return 'UNKNOWN';
    }
}
