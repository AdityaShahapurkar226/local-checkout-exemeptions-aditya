<?php

declare(strict_types=1);

namespace Ahy\Authorizenet\Controller\Vault;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory as PaymentTokenCollectionFactory;
use Ahy\Authorizenet\Model\CustomerProfileRepository;
use Psr\Log\LoggerInterface;

class CardInfo extends Action
{
    private JsonFactory $resultJsonFactory;
    private PaymentTokenCollectionFactory $paymentTokenCollectionFactory;
    private CustomerProfileRepository $customerProfileRepository;
    private LoggerInterface $logger;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PaymentTokenCollectionFactory $paymentTokenCollectionFactory,
        CustomerProfileRepository $customerProfileRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->paymentTokenCollectionFactory = $paymentTokenCollectionFactory;
        $this->customerProfileRepository = $customerProfileRepository;
        $this->logger = $logger;
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $publicHash = (string) $this->getRequest()->getParam('public_hash');

        $this->logger->info('[VaultCheckout] CardInfo request received.', [
            'public_hash' => $publicHash
        ]);

        if ($publicHash === '') {
            $this->logger->warning('[VaultCheckout] Missing public hash.');
            return $result->setData(['success' => false, 'message' => 'Missing public hash']);
        }

        $token = $this->paymentTokenCollectionFactory->create()
            ->addFieldToFilter('public_hash', $publicHash)
            ->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('is_visible', 1)
            ->getFirstItem();

        if (!$token->getId()) {
            $this->logger->warning('[VaultCheckout] No token found for public hash.', [
                'public_hash' => $publicHash
            ]);
            return $result->setData(['success' => false, 'message' => 'Token not found']);
        }

        $customerId = (int) $token->getCustomerId();
        $gatewayToken = (string) $token->getGatewayToken();

        $this->logger->info('[VaultCheckout] Token found.', [
            'customer_id' => $customerId,
            'gateway_token' => $gatewayToken
        ]);

        $customerProfileId = $this->customerProfileRepository->getProfileIdByCustomerId($customerId);

        if (!$customerProfileId) {
            $this->logger->warning('[VaultCheckout] No customer profile found.', [
                'customer_id' => $customerId
            ]);
            return $result->setData(['success' => false, 'message' => 'Customer profile not found']);
        }

        $this->logger->info('[VaultCheckout] CardInfo resolved successfully.', [
            'customer_profile_id' => $customerProfileId,
            'customer_payment_profile_id' => $gatewayToken
        ]);

        return $result->setData([
            'success' => true,
            'customerProfileId' => $customerProfileId,
            'customerPaymentProfileId' => $gatewayToken
        ]);
    }
}