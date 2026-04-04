<?php

namespace Webkul\Marketplace\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Ahy\Authorizenet\Service\AuthorizeNetCimService;

class UpdateVaultToken extends Action
{
    protected $paymentTokenRepository;
    protected $jsonSerializer;
    protected $cimService;

    public function __construct(
        Action\Context $context,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        Json $jsonSerializer,
        AuthorizeNetCimService $cimService
    ) {
        parent::__construct($context);
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->jsonSerializer = $jsonSerializer;
        $this->cimService = $cimService;
    }

    public function execute()
    {
        $request = $this->getRequest();
        $customerId = (int) $request->getParam('customer_id');
        $tokenId = (int) $request->getParam('token_id');
        $paymentProfileId = $request->getParam('payment_profile_id');

        try {
            $vaultToken = $this->paymentTokenRepository->getById($tokenId);
            if (!$vaultToken || (int) $vaultToken->getCustomerId() !== $customerId) {
                throw new LocalizedException(__('Invalid token or customer.'));
            }

            $cardNumber = preg_replace('/\D/', '', $request->getParam('card_number'));
            $expMonth = $request->getParam('exp_month');
            $expYear = $request->getParam('exp_year');
            $expirationDate = sprintf('%s-%s', $expYear, str_pad($expMonth, 2, '0', STR_PAD_LEFT));

            $data = [
                'customerProfileId'   => $this->cimService->getCustomerProfileIdByCustomerId($customerId),
                'paymentProfileId'    => $paymentProfileId,
                'cardNumber'          => $cardNumber,
                'expirationDate'      => $expirationDate,
                'cvv'                 => $request->getParam('cvv'),
                'billing_first_name'  => $request->getParam('billing_first_name'),
                'billing_last_name'   => $request->getParam('billing_last_name'),
                'billing_street'      => $request->getParam('billing_street'),
                'billing_city'        => $request->getParam('billing_city'),
                'billing_state'       => $request->getParam('billing_state'),
                'billing_zip'         => $request->getParam('billing_zip'),
                'billing_country'     => $request->getParam('billing_country'),
                'billing_phone'       => $request->getParam('billing_phone'),
            ];

            if (empty($data['customerProfileId'])) {
                throw new LocalizedException(__('Customer profile ID not found.'));
            }

            // Call Authorize.Net API to update card
            $this->cimService->updateCustomerPaymentProfile($data);

            // Prepare vault details
            $details = [
                'type' => $this->detectCardType($cardNumber),
                'maskedCC' => substr($cardNumber, -4),
                'expirationDate' => sprintf('%s/%s', str_pad($expMonth, 2, '0', STR_PAD_LEFT), $expYear),
            ];

            $vaultToken->setDetails(json_encode($details, JSON_UNESCAPED_SLASHES));

            // Set expires_at to last day of month at 23:59:59
            $expiresAt = new \DateTimeImmutable();
            $expiresAt = $expiresAt
                ->setDate((int)$expYear, (int)$expMonth, 1)
                ->modify('last day of this month')
                ->setTime(23, 59, 59);

            $vaultToken->setExpiresAt($expiresAt->format('Y-m-d H:i:s'));

            $this->paymentTokenRepository->save($vaultToken);

            $this->messageManager->addSuccessMessage(__('The card has been successfully updated.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Failed to update card: %1', $e->getMessage()));
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath(
            'customer/index/edit',
            ['id' => $customerId, '_current' => true]
        );
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
