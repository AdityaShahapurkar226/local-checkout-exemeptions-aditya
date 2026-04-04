<?php

namespace Ahy\Authorizenet\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Vault\Model\PaymentTokenFactory;
use Ahy\Authorizenet\Service\AuthorizeNetCimService;
use Magento\Customer\Api\AddressRepositoryInterface;
use Ahy\Authorizenet\Helper\Decryptor;

class UpdateSavedCard extends Action
{
    protected $customerSession;
    protected $redirectFactory;
    protected $messageManager;
    protected $request;
    protected $paymentTokenRepository;
    protected $encryptor;
    protected $dateTime;
    protected $paymentTokenFactory;
    protected $cimService;
    protected $addressRepository;
    protected $decryptor;

    public function __construct(
        Context $context,
        Http $request,
        Session $customerSession,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        PaymentTokenFactory $paymentTokenFactory,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        EncryptorInterface $encryptor,
        DateTime $dateTime,
        AuthorizeNetCimService $cimService,
        AddressRepositoryInterface $addressRepository,
        Decryptor $decryptor

    ) {
        parent::__construct($context);

        $this->request                = $request;
        $this->customerSession        = $customerSession;
        $this->redirectFactory        = $redirectFactory;
        $this->messageManager         = $messageManager;
        $this->paymentTokenFactory    = $paymentTokenFactory;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->encryptor              = $encryptor;
        $this->dateTime               = $dateTime;
        $this->cimService             = $cimService;
        $this->addressRepository      = $addressRepository;
        $this->decryptor              = $decryptor;
    }

    public function execute()
    {
        $params   = $this->request->getParams();
        $redirect = $this->redirectFactory->create()->setPath('customer/account');
        $customerId = $this->customerSession->getCustomerId();

        if (!$customerId) {
            return $this->redirectFactory->create()->setPath('customer/account/login');
        }

        try {
            $paymentProfileId = $params['payment_profile_id'] ?? null;
            $tokenEntityId    = $params['vault_token_id'] ?? null;

            if (!$paymentProfileId || !$tokenEntityId) {
                throw new \Exception(__('Missing payment profile or token ID.'));
            }
            $params = $this->getRequest()->getParams();
            $secretKey = 'my_static_secret_key';
            $cardNumberEnc = $params['card_number'] ?? '';
            $cardNumber = $this->decryptor->decrypt($cardNumberEnc, $secretKey);
            $expMonthEnc   = $params['expiration_month'] ?? '';
            $expMonth = $this->decryptor->decrypt($expMonthEnc, $secretKey);
            $expYearEnc    = $params['expiration_year'] ?? '';
            $expYear = $this->decryptor->decrypt($expYearEnc, $secretKey);
            $cvvEnc = $params['cvv'] ?? '';
            $cvv = $this->decryptor->decrypt($cvvEnc, $secretKey);
            $addressId  = (int)($params['billing_address_id'] ?? 0);

            $expirationDate = sprintf('%s-%s', $expYear, str_pad($expMonth, 2, '0', STR_PAD_LEFT));

            $address     = $this->addressRepository->getById($addressId);
            $region      = $address->getRegion();
            $regionCode  = is_object($region) ? $region->getRegionCode() : $region;

            $billTo = [
                'billing_first_name' => $address->getFirstname(),
                'billing_last_name'  => $address->getLastname(),
                'billing_street'     => implode(' ', $address->getStreet()),
                'billing_city'       => $address->getCity(),
                'billing_state'      => $regionCode,
                'billing_zip'        => $address->getPostcode(),
                'billing_country'    => $address->getCountryId(),
                'billing_phone'      => $address->getTelephone(),
            ];

            // Fetch customerProfileId dynamically
            $customerProfileId = $this->cimService->getCustomerProfileIdByCustomerId((int) $customerId);

            if (!$customerProfileId) {
                throw new \Exception('Customer profile ID not found.');
            }

            // Call update API
            $this->cimService->updateCustomerPaymentProfile([
                'customerProfileId' => $customerProfileId,
                'paymentProfileId'  => $paymentProfileId,
                'cardNumber'        => $cardNumber,
                'expirationDate'    => $expirationDate,
                'cvv'               => $cvv,
                ...$billTo,
            ]);

            // Update Vault token locally
            $token = $this->paymentTokenRepository->getById($tokenEntityId);
            $token->setExpiresAt(date('Y-m-t 23:59:59', strtotime($expirationDate)));

            $details = json_decode($token->getTokenDetails() ?? '{}', true);

            $details['expirationDate'] = sprintf('%02d/%s', $expMonth, $expYear);
            $details['maskedCC']       = substr($cardNumber, -4);
            $details['type']           = $this->detectCardType($cardNumber);

            $token->setTokenDetails(json_encode($details, JSON_UNESCAPED_SLASHES));
            $this->paymentTokenRepository->save($token);

            $this->messageManager->addSuccessMessage(__('Card updated successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Card update failed: ') . $e->getMessage());
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