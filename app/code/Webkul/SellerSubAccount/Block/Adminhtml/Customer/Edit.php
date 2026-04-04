<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Block\Adminhtml\Customer;

use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;

class Edit extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var null
     */
    protected $_objectManager = null;

    /**
     * @var CountryCollection
     */
    protected $_country;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param CountryCollection $country
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        CountryCollection $country,
        \Magento\Directory\Model\Currency $currency,
        \Webkul\Marketplace\Helper\Data $helper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectManager;
        $this->_helper = $helper;
        $this->_country = $country;
        $this->_currency = $currency;
        parent::__construct($context, $data);
    }

    /**
     * Get Seller Info Collection
     *
     * @return array
     */
    public function getSellerInfoCollection()
    {
        $customerId = $this->getRequest()->getParam('id');
        $data = [];
        if ($customerId != '') {
            $collection = $this->_objectManager->create(
                \Webkul\Marketplace\Model\Seller::class
            )->getCollection()
            ->addFieldToFilter('seller_id', $customerId);
            $user = $this->_objectManager->get(
                \Magento\Customer\Model\Customer::class
            )->load($customerId);
            $name = explode(' ', $user->getName());
            foreach ($collection as $record) {
                $data = $record->getData();
                $bannerpic = $record->getBannerPic();
                $logopic = $record->getLogoPic();
                $countrylogopic = $record->getCountryPic();
                if (strlen($bannerpic) <= 0) {
                    $bannerpic = 'banner-image.png';
                }
                if (strlen($logopic) <= 0) {
                    $logopic = 'noimage.png';
                }
                if (strlen($countrylogopic) <= 0) {
                    $countrylogopic = '';
                }
            }
            $data['firstname'] = $name[0];
            $data['lastname'] = $name[1];
            $data['email'] = $user->getEmail();
            $data['banner_pic'] = $bannerpic;
            $data['logo_pic'] = $logopic;
            $data['country_pic'] = $countrylogopic;

            return $data;
        }
    }

    /**
     * Get Country List
     *
     * @return array
     */
    public function getCountryList()
    {
        return $this->_country->loadByStore()->toOptionArray(true);
    }

    /**
     * Get Payment Mode
     *
     * @return string
     */
    public function getPaymentMode()
    {
        $customerId = $this->getRequest()->getParam('id');
        $collection = $this->_objectManager->create(
            \Webkul\Marketplace\Model\Seller::class
        )->getCollection()
        ->addFieldToFilter('seller_id', $customerId);
        $data = '';
        foreach ($collection as $record) {
            $data = $record->getPaymentSource();
        }

        return $data;
    }

    /**
     * Get Sales Partner Collection
     *
     * @return Webkul\Marketplace\Model\Saleperpartner
     */
    public function getSalesPartnerCollection()
    {
        $customerId = $this->getRequest()->getParam('id');

        $collection = $this->_objectManager->create(
            \Webkul\Marketplace\Model\Saleperpartner::class
        )->getCollection()
        ->addFieldToFilter('seller_id', $customerId);

        return $collection;
    }

    /**
     * Get Sales List Collection
     *
     * @return Webkul\Marketplace\Model\Saleslist
     */
    public function getSalesListCollection()
    {
        $customerId = $this->getRequest()->getParam('id');

        $collection = $this->_objectManager->create(
            \Webkul\Marketplace\Model\Saleslist::class
        )->getCollection()
        ->addFieldToFilter('seller_id', $customerId);

        return $collection;
    }

    /**
     * Get Config Commission Rate
     *
     * @return string
     */
    public function getConfigCommissionRate()
    {
        return $this->_helper->getConfigCommissionRate();
    }

    /**
     * Get Currency Symbol
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    }

    /**
     * Get Product Collection
     *
     * @return Webkul\Marketplace\Model\Product
     */
    public function getProductCollection()
    {
        $customerId = $this->getRequest()->getParam('id');

        $collection = $this->_objectManager->create(
            \Webkul\Marketplace\Model\Product::class
        )->getCollection()
        ->addFieldToFilter('seller_id', $customerId)
        ->addFieldToFilter('adminassign', 1);

        return $collection;
    }

    /**
     * Get Marketplace User Collection
     *
     * @return Webkul\Marketplace\Model\Seller
     */
    public function getMarketplaceUserCollection()
    {
        $customerId = $this->getRequest()->getParam('id');
        $collection = $this->_objectManager->create(
            \Webkul\Marketplace\Model\Seller::class
        )->getCollection()
        ->addFieldToFilter('seller_id', $customerId);

        return $collection;
    }

    /**
     * Get All Customer Collection
     *
     * @return object
     */
    public function getAllCustomerCollection()
    {
        $collection = $this->_objectManager->create(
            \Magento\Customer\Model\Customer::class
        )->getCollection();

        return $collection;
    }
}
