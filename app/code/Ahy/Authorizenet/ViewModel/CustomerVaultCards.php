<?php
namespace Ahy\Authorizenet\ViewModel;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory as TokenCollectionFactory;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class CustomerVaultCards implements ArgumentInterface
{
    protected $customerSession;
    protected $tokenCollectionFactory;

    public function __construct(
        CustomerSession $customerSession,
        TokenCollectionFactory $tokenCollectionFactory
    ) {
        $this->customerSession = $customerSession;
        $this->tokenCollectionFactory = $tokenCollectionFactory;
    }

    /**
     * For checkout: fetch only active and visible cards.
     */
    public function getCustomerTokens(): array
    {
        $customerId = $this->customerSession->getCustomerId();

        $collection = $this->tokenCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->addFieldToFilter('is_visible', 1);
        $collection->addFieldToFilter('is_active', 1); // used for checkout only
        $collection->addOrder('created_at', 'DESC');

        return $collection->getItems();
    }

    /**
     * For dashboard: fetch all visible cards (active + expired).
     */
    public function getAllVisibleCustomerTokens(): array
    {
        $customerId = $this->customerSession->getCustomerId();

        $collection = $this->tokenCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->addFieldToFilter('is_visible', 1);
        $collection->addOrder('created_at', 'DESC');

        return $collection->getItems();
    }

    public function getCardDetails($token): array
    {
        $details = json_decode($token->getTokenDetails(), true) ?? [];

        return [
            'type' => $details['type'] ?? 'N/A',
            'maskedCC' => $details['maskedCC'] ?? '****',
            'expirationDate' => $details['expirationDate'] ?? 'N/A',
            'gatewayToken' => $token->getGatewayToken(),
            'createdAt' => $token->getCreatedAt(),
            'entityId' => $token->getEntityId(),
            'publicHash' => $token->getPublicHash(),
            'isActive' => (bool) $token->getIsActive(),
        ];
    }
}
