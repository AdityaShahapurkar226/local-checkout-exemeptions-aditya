<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpFixedRateshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpFixedRateshipping\Helper;

/**
 * MpFixedRateshipping data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var string
     */
    protected $_code = 'mpfixrate';

    /**
     * Core store config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $_localeCurrency;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
        $this->_customerFactory = $customerFactory;
        $this->_storeManager = $storeManager;
        $this->_currency = $currency;
        $this->_localeCurrency = $localeCurrency;
    }

    /**
     * Get shipping charge from product collection.
     *
     * @param getShippingCharges $sellerId
     * @return int
     */
    public function getShippingCharges($sellerId)
    {
        $shippingData = $this->_customerFactory
                                ->create()
                                ->load($sellerId)
                                ->getMpshippingFixrate();

        return $shippingData;
    }

    /**
     * Get shipping charge from product collection.
     *
     * @param getFreeShippingFrom $sellerId
     * @return int
     */
    public function getFreeShippingFrom($sellerId)
    {
        $amount = $this->_customerFactory
                                ->create()
                                ->load($sellerId)
                                ->getMpshippingFixrateUpto();

        return $amount;
    }

    /**
     * Retrieve information from carrier configuration.
     *
     * @param getConfigData $field
     * @param getConfigData $code
     * @return void|false|string
     */
    public function getConfigData($field, $code = '')
    {
        if ($code != '') {
            $path = 'carriers/'.$code.'/'.$field;
        } else {
            if (empty($this->_code)) {
                return false;
            }
            $path = 'carriers/'.$this->_code.'/'.$field;
        }

        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()
        );
    }

    /**
     * Get Currency Symbol
     *
     * @param string $currencycode
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        $currency = $this->_localeCurrency->getCurrency($this->getCurrentCurrencyCode());

        return $currency->getSymbol() ? $currency->getSymbol() : $currency->getShortName();
    }

    /**
     * Get current currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Get base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }

    /**
     * Get currency rate.
     *
     * @param string $currency
     * @param string $toCurrencies
     *
     * @return float
     */
    public function getCurrencyRates($currency, $toCurrencies = null)
    {
        return $this->_currency->getCurrencyRates($currency, $toCurrencies); // give the currency rate
    }

    /**
     * Get allowed currencies list
     *
     * @return []
     */
    public function getAllowedCurrencies()
    {
        return $this->_currency->getConfigAllowCurrencies();
    }
}
