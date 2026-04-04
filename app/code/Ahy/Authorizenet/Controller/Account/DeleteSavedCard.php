<?php

namespace Ahy\Authorizenet\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\Request\Http;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Ahy\Authorizenet\Service\AuthorizeNetCimService;

class DeleteSavedCard extends Action
{
    protected $customerSession;
    protected $messageManager;
    protected $request;
    protected $paymentTokenRepository;
    protected $cimService;

    public function __construct(
        Context $context,
        Http $request,
        Session $customerSession,
        ManagerInterface $messageManager,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        AuthorizeNetCimService $cimService
    ) {
        parent::__construct($context);
        $this->request = $request;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->cimService = $cimService;
    }

    public function execute()
    {
        $customerId = $this->customerSession->getCustomerId();
        if (!$customerId) {
            return $this->_redirect('customer/account/login');
        }

        try {
            $vaultTokenId = $this->request->getParam('vault_token_id');
            $paymentProfileId = $this->request->getParam('payment_profile_id');

            $token = $this->paymentTokenRepository->getById($vaultTokenId);
            $details = json_decode($token->getTokenDetails(), true);
            $customerProfileId = $details['customerProfileId'] ?? null;

            if (!$vaultTokenId || !$paymentProfileId) {
                throw new \Exception('Missing payment profile or token ID.');
            }

            $customerProfileId = $this->cimService->getCustomerProfileIdByCustomerId((int)$customerId);
            if (!$customerProfileId) {
                throw new \Exception('Customer profile ID not found.');
            }

            // Call CIM delete API
            $this->cimService->deleteCustomerPaymentProfile($customerProfileId, $paymentProfileId);

            // Delete from Vault
            $token = $this->paymentTokenRepository->getById($vaultTokenId);
            $this->paymentTokenRepository->delete($token);

            $this->messageManager->addSuccessMessage(__('Card deleted successfully.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Failed to delete card: ') . $e->getMessage());
        }

        return $this->_redirect('customer/account');
    }
}
