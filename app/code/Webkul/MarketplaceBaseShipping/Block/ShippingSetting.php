<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MarketplaceBaseShipping
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceBaseShipping\Block;

use Magento\Catalog\Model\Product;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;

class ShippingSetting extends \Magento\Directory\Block\Data
{

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $dHelper;

    /**
     * @var \Magento\Customer\Helper\Address
     */
    protected $customerHelper;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    protected $_address;

    /**
     * @param \Magento\Catalog\Block\Product\Context             $context
     * @param \Webkul\MpFedexShipping\Helper\Data                $currentHelper
     * @param \Magento\Customer\Model\Session                    $customerSession
     * @param \Magento\Config\Model\Config\Source\Yesno          $yesNo
     * @param \Magento\Framework\Registry                        $coreRegistry
     * @param \Magento\Directory\Helper\Data                     $dHelper
     * @param \Magento\Customer\Helper\Address                   $customerHelper
     * @param array                                              $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Directory\Helper\Data $dHelper,
        \Magento\Customer\Helper\Address $customerHelper,
        array $data = []
    ) {
        $this->marketplaceHelper = $marketplaceHelper;
        $this->_customerSession = $customerSession;
        $this->_coreRegistry = $coreRegistry;
        $this->dHelper = $dHelper;
        $this->customerHelper = $customerHelper;
        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $data
        );
    }

    /**
     * Prepare global layout.
     *
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->_address = $this->_coreRegistry->registry('shipping_setting');
        $this->setData('country_id', $this->_address->getCountryId());
    }
     /**
      * Return the associated address.
      *
      * @return \Webkul\MarketplaceBaseShipping\Model\ShippingSetting
      */
    public function getAddress()
    {
        return $this->_address;
    }

    /**
     * Return the name of the region for the address being edited.
     *
     * @return string region name
     */
    public function getRegion()
    {
        $region = $this->getAddress()->getRegion();
        return $region === null ? '' : $region;
    }

    /**
     * Return the name of the region for the address being edited.
     *
     * @return string region name
     */
    public function getRegionId()
    {
        $regionId = $this->getAddress()->getRegionId();
        return $regionId === null ? '' : $regionId;
    }

    /**
     * Return the specified numbered street line.
     *
     * @param int $lineNumber
     * @return string
     */
    public function getStreetLine($lineNumber)
    {
        $street = $this->_address->getStreet();
        return isset($street[$lineNumber - 1]) ? $street[$lineNumber - 1] : '';
    }

    /**
     * Return the Url for saving.
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl(
            'baseshipping/shipping/formPost',
            ['_secure' => true]
        );
    }

    /**
     * Get Directory helper
     *
     * @return dHelper
     */
    public function getDHelper()
    {
        return $this->dHelper;
    }

    /**
     * Get Customer helper
     *
     * @return customerHelper
     */
    public function getCustomerHelper()
    {
        // echo $this->customerHelper->getAttributeValidationClass('street'); die("====");
        return $this->customerHelper;
    }
}
