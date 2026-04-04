<?php
namespace Ahy\Authorizenet\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class ConfigProvider
{
    private $_scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;
    }

    public function isPaymentMethodEnabled()
    {
        return (bool)$this->_scopeConfig->getValue('payment/authnetahypayment/active');
    }
    public function getLogin()
    {
        return $this->_scopeConfig->getValue('payment/authnetahypayment/login');
    }

    public function getTransKey()
    {
        return $this->_scopeConfig->getValue('payment/authnetahypayment/trans_key');
    }
    public function getAccountType()
    {
        return $this->_scopeConfig->getValue('payment/authnetahypayment/accountType');
    }
}
