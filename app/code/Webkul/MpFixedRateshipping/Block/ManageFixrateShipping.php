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
namespace Webkul\MpFixedRateshipping\Block;

use Magento\Catalog\Model\Product;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Webkul\MpFixedRateshipping\Helper\Data as MpFixedRateHelper;

/**
 * Webkul Mpfixrateshipping Product Create Block.
 *
 * @author      Webkul Software
 */
class ManageFixrateShipping extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * Core store config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var string
     */
    protected $_code = 'mpfixrate';

    /**
     * @var CollectionFactory
     */
    protected $_countryCollection;

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_localeLists;

    /**
     * @var \Webkul\Mpfixrateshipping\Helper\Data
     */
    protected $_currentHelper;

    /**
     * @var SessionFactory
     */
    protected $_customerSessionFactory;

    /**
     * @var PricingHelper
     */
    protected $pricingHelper;

    /**
     * @var MarketplaceHelper
     */
    protected $marketplaceHelper;

    /**
     * @var MpFixedRateHelper
     */
    protected $mpFixedRateHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param CollectionFactory $countryCollection
     * @param Product $product
     * @param \Webkul\MpFixedRateshipping\Helper\Data $currentHelper
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Magento\Directory\Model\Currency $currency
     * @param PricingHelper $pricingHelper
     * @param MarketplaceHelper $marketplaceHelper
     * @param MpFixedRateHelper $mpFixedRateHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        CollectionFactory $countryCollection,
        Product $product,
        \Webkul\MpFixedRateshipping\Helper\Data $currentHelper,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Directory\Model\Currency $currency,
        PricingHelper $pricingHelper,
        MarketplaceHelper $marketplaceHelper,
        MpFixedRateHelper $mpFixedRateHelper,
        array $data = []
    ) {
        $this->_product = $product;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_localeLists = $localeLists;
        $this->_countryCollection = $countryCollection;
        $this->_currentHelper = $currentHelper;
        $this->_customerSessionFactory = $customerSessionFactory;
        $this->currency = $currency;
        $this->pricingHelper = $pricingHelper;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->mpFixedRateHelper = $mpFixedRateHelper;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve information from carrier configuration.
     *
     * @param string $field
     *
     * @return void|false|string
     */
    public function getConfigData($field)
    {
        return $this->getHelper()->getConfigData($field);
    }

    /**
     * Get current module helper.
     *
     * @return \Webkul\Mpfixrateshipping\Helper\Data
     */
    public function getHelper()
    {
        return $this->_currentHelper;
    }
    /**
     * Get Currenty Format.
     *
     * @param getCurrentyFormat $price
     */
    public function getCurrentyFormat($price)
    {
        return $this->currency->format($price, ['display'=>\Zend_Currency::NO_SYMBOL], false);
    }

    /**
     * Return current customer session.
     *
     * @return \Magento\Customer\Model\Session
     */
    public function getCustomerData()
    {
        return $this->_customerSessionFactory->create()->getCustomer();
    }

    /**
     * Get Helper Object
     *
     * @param getHelperObject $helper
     * @return object
     */
    public function getHelperObject($helper = '')
    {
        if ($helper == 'pricingHelper') {
            return $this->pricingHelper;
        } elseif ($helper  == 'marketplaceHelper') {
            return $this->marketplaceHelper;
        } elseif ($helper  == 'mpFixedRateHelper') {
            return $this->mpFixedRateHelper;
        }
    }
}
