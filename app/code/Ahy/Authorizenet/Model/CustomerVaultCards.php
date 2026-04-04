<?php
namespace Ahy\Authorizenet\Model;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Vault\Model\ResourceModel\PaymentToken\CollectionFactory;
use Ahy\SavedCC\Helper\Config as SavedCCConfig;


class CustomerVaultCards extends AbstractMethod
{
    protected $_code = 'customervaultcards';
    protected CollectionFactory $paymentTokenCollectionFactory;
    protected SavedCCConfig $configHelper;
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        CollectionFactory $paymentTokenCollectionFactory,
        SavedCCConfig $configHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            null,
            null,
            $data
        );

        $this->paymentTokenCollectionFactory = $paymentTokenCollectionFactory;
        $this->configHelper = $configHelper;
    }
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null): bool
    {
        // First check if module is enabled in config
        if (!$this->configHelper->isEnabled()) {
            return false;
        }

        if (!$quote || !$quote->getCustomerId()) {
            return false;
        }

        $customerId = $quote->getCustomerId();

        $collection = $this->paymentTokenCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->addFieldToFilter('is_active', 1);
        $collection->addFieldToFilter('is_visible', 1);
        $collection->addFieldToFilter('payment_method_code', 'authnetahypayment'); // fixed here

        return (bool) $collection->getSize();
    }
}
