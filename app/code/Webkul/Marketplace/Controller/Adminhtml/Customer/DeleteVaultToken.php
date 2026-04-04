<?php

namespace Webkul\Marketplace\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Ahy\Authorizenet\Service\AuthorizeNetCimService;
use Magento\Framework\App\ResourceConnection;

class DeleteVaultToken extends Action
{
    protected $tokenRepository;
    protected $cimService;
    protected $resourceConnection;

    public function __construct(
        Action\Context $context,
        PaymentTokenRepositoryInterface $tokenRepository,
        AuthorizeNetCimService $cimService,
        ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->tokenRepository = $tokenRepository;
        $this->cimService = $cimService;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute()
    {
        $customerId = (int) $this->getRequest()->getParam('customer_id');
        $tokenId = (int) $this->getRequest()->getParam('token_id');

        try {
            if ($tokenId && $customerId) {
                $token = $this->tokenRepository->getById($tokenId);

                if ($token && (int) $token->getCustomerId() === $customerId) {
                    // Step 1: Get customerProfileId from custom table
                    $connection = $this->resourceConnection->getConnection();
                    $select = $connection->select()
                        ->from('ahy_authorizenet_customer_profile', ['customer_profile_id'])
                        ->where('customer_id = ?', $customerId)
                        ->limit(1);
                    $customerProfileId = $connection->fetchOne($select);

                    if (!$customerProfileId) {
                        throw new \Exception('Customer profile ID not found for this customer.');
                    }

                    // Step 2: Get paymentProfileId from vault token
                    $paymentProfileId = $token->getGatewayToken();

                    // Step 3: Call Authorize.Net delete API
                    $this->cimService->deleteCustomerPaymentProfile($customerProfileId, $paymentProfileId);

                    // Step 4: Delete from Vault
                    $this->tokenRepository->delete($token);

                    $this->messageManager->addSuccessMessage(__('The saved card has been deleted from Magento and Authorize.Net.'));
                } else {
                    $this->messageManager->addErrorMessage(__('Token not found or does not belong to this customer.'));
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error deleting token: %1', $e->getMessage()));
        }

        return $this->_redirect('customer/index/edit', [
            'id' => $customerId,
            'active_tab' => 'marketplace_customer_edit_tab_paymentinfo'
        ]);
    }
}
