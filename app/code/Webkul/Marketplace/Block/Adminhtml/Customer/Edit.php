<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Block\Adminhtml\Customer;

use Magento\Customer\Model\CustomerFactory;
use Webkul\Marketplace\Model\SellerFactory;
use Webkul\Marketplace\Model\SaleperpartnerFactory;
use Webkul\Marketplace\Model\SaleslistFactory;
use Webkul\Marketplace\Model\ProductFactory;

use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;

class Edit extends \Magento\Backend\Block\Widget
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

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
     * @var CustomerFactory
     */
    protected $customerModel;

    /**
     * @var SellerFactory
     */
    protected $sellerModel;

    /**
     * @var SaleperpartnerFactory
     */
    protected $saleperpartner;

    /**
     * @var SaleslistFactory
     */
    protected $saleslistFactory;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * Construct
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param CountryCollection $country
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param CustomerFactory $customerModel
     * @param SellerFactory $sellerModel
     * @param SaleperpartnerFactory $saleperpartner
     * @param SaleslistFactory $saleslistFactory
     * @param ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        CountryCollection $country,
        \Magento\Directory\Model\Currency $currency,
        \Webkul\Marketplace\Helper\Data $helper,
        CustomerFactory $customerModel,
        SellerFactory $sellerModel,
        SaleperpartnerFactory $saleperpartner,
        SaleslistFactory $saleslistFactory,
        ProductFactory $productFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_helper = $helper;
        $this->_country = $country;
        $this->_currency = $currency;
        $this->customerModel = $customerModel;
        $this->sellerModel = $sellerModel;
        $this->saleperpartner = $saleperpartner;
        $this->saleslistFactory = $saleslistFactory;
        $this->productFactory = $productFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get seller info collection
     *
     * @return array
     */
    public function getSellerInfoCollection()
    {
        $customerId = $this->getRequest()->getParam('id');
        $requestParams = $this->getRequest()->getParams();
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        $data = [];
        if ($customerId != '') {
            $user = $this->customerModel->create()->load($customerId);
            if (!isset($requestParams['store'])) {
                $storeId = $user->getStoreId();
            }
            $collection = $this->sellerModel->create()
                          ->getCollection()
                          ->addFieldToFilter('seller_id', $customerId)
                          ->addFieldToFilter('store_id', $storeId);
            if (!count($collection)) {
                $collection = $this->sellerModel->create()
                ->getCollection()
                ->addFieldToFilter('seller_id', $customerId)
                ->addFieldToFilter('store_id', 0);
            }
            $name = explode(' ', $user->getName());
            foreach ($collection as $record) {
                $data = $record->getData();
                $bannerpic = $record->getBannerPic() ? $record->getBannerPic() :'' ;
                $logopic = $record->getLogoPic() ? $record->getLogoPic() : '';
                $countrylogopic = $record->getCountryPic() ? $record->getCountryPic() :"";
                if (strlen($bannerpic) <= 0) {
                    $bannerpic = $this->_helper->getProfileBannerImage();
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
    * Get country list
    *
    * @return array
    */
    public function getCountryList()
    {
        return $this->_country->loadByStore()->toOptionArray(true);
    }
    /**
     * Get payment mode
     *
     * @return string
     */
    public function getPaymentMode()
    {
        $customerId = $this->getRequest()->getParam('id');
        $collection = $this->sellerModel->create()
                      ->getCollection()
                      ->addFieldToFilter('seller_id', $customerId);
        $data = '';
        foreach ($collection as $record) {
            $data = $record->getPaymentSource();
        }

        return $data;
    }

    /**
     * Get sales partner getCollection
     *
     * @return Webkul\Marketplace\Model\Saleperpartner
     */
    public function getSalesPartnerCollection()
    {
        $customerId = $this->getRequest()->getParam('id');

        $collection =$this->saleperpartner->create()
                    ->getCollection()
                    ->addFieldToFilter('seller_id', $customerId);

        return $collection;
    }
    /**
     * Get saleslist
     *
     * @return Webkul\Marketplace\Model\Saleslist
     */
    public function getSalesListCollection()
    {
        $customerId = $this->getRequest()->getParam('id');

        $collection = $this->saleslistFactory->create()
                      ->getCollection()
                      ->addFieldToFilter('seller_id', $customerId);

        return $collection;
    }
    /**
     * Get configu commission
     *
     * @return string
     */
    public function getConfigCommissionRate()
    {
        return $this->_helper->getConfigCommissionRate();
    }
    /**
     * Get currency symbol
     *
     * @param Decimal $price
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    }
    /**
     * Get product collection
     *
     * @return Webkul\Marketplace\Model\Product
     */
    public function getProductCollection()
    {
        $customerId = $this->getRequest()->getParam('id');

        $collection = $this->productFactory->create()
                      ->getCollection()
                      ->addFieldToFilter('seller_id', $customerId)
                      ->addFieldToFilter('adminassign', 1);

        return $collection;
    }
    /**
     * Get marketplace user collection
     *
     * @return Webkul\Marketplace\Model\Seller
     */
    public function getMarketplaceUserCollection()
    {
        $customerId = $this->getRequest()->getParam('id');
        $collection = $this->sellerModel->create()
                      ->getCollection()
                      ->addFieldToFilter('seller_id', $customerId)
                      ->addFieldToFilter('is_seller', 1);

        return $collection;
    }

    /**
     * Get marketplace seller collection
     *
     * @return Webkul\Marketplace\Model\Seller
     */
    public function getMarketplaceSellerCollection()
    {
        $customerId = $this->getRequest()->getParam('id');
        $collection = $this->sellerModel->create()
                      ->getCollection()
                      ->addFieldToFilter('seller_id', $customerId);
        return $collection;
    }

    /**
     * Get all customer collection
     *
     * @return void
     */
    public function getAllCustomerCollection()
    {
        $collection = $this->customerModel->create()->getCollection();

        return $collection;
    }
}
