<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Customattribute
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Customattribute\Block;

use Magento\Customer\Model\Session;

/**
 * Webkul Customattribute Manage Attribute Block.
 */
class Manageattribute extends \Magento\Framework\View\Element\Template
{
    /**
     * @var $_countries
     */
    protected $_countries = null;
    /**
     * @var Session
     */
    protected $customerSession;
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection
     */
    protected $_attributeGroupCollection;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    protected $_productAttributeCollection;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;
    /**
     * Websites cache.
     *
     * @var array
     */
    protected $_websites;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timeZone;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $attributeGroup
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $productAttribute
     * @param \Webkul\Customattribute\Model\ManageattributeFactory $attributeManagerFactory
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
     * @param \Magento\Customer\Model\GroupFactory $groupModelFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Directory\Model\Currency $currencyModel
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magento\Directory\Model\Config\Source\Country $sourceCountry
     * @param \Magento\Cms\Helper\Wysiwyg\Images $wysiwygImages
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timeZone
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $attributeGroup,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $productAttribute,
        \Webkul\Customattribute\Model\ManageattributeFactory $attributeManagerFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Magento\Customer\Model\GroupFactory $groupModelFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Directory\Model\Currency $currencyModel,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Directory\Model\Config\Source\Country $sourceCountry,
        \Magento\Cms\Helper\Wysiwyg\Images $wysiwygImages,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timeZone,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->_attributeGroupCollection = $attributeGroup;
        $this->_productAttributeCollection = $productAttribute;
        $this->_storeManager = $context->getStoreManager();
        $this->_directoryHelper = $directoryHelper;
        $this->_attributeManagerFactory = $attributeManagerFactory;
        $this->_attributeFactory = $attributeFactory;
        $this->_groupModelFactory = $groupModelFactory;
        $this->_productFactory = $productFactory;
        $this->_currencyModel = $currencyModel;
        $this->_mpHelper = $mpHelper;
        $this->_jsonHelper = $jsonHelper;
        $this->_priceHelper = $priceHelper;
        $this->_sourceCountry = $sourceCountry;
        $this->wysiwygImages = $wysiwygImages;
        $this->timeZone = $timeZone;
        parent::__construct($context, $data);
    }
    /**
     * Get attribute set
     */
    public function getAttributeSet()
    {
        return $this->customerSession->getAttributeSet();
    }
    /**
     * Collect all custom attribute if status visible
     *
     * @param  int $attributeSetId
     * @return \Webkul\Customattribute\Model\Manageattribute $readresult
     */
    public function getFrontShowAttributes($attributeSetId)
    {
        $attributes = [];
        $groups = $this->_attributeGroupCollection->create()
            ->setAttributeSetFilter($attributeSetId)
            ->setSortOrder()
            ->load();
        $attributeids = [];
        foreach ($groups as $node) {
            $nodeChildren = $this->_productAttributeCollection->create()
                ->setAttributeGroupFilter($node->getId())
                ->addVisibleFilter()
                ->load();
            if ($nodeChildren->getSize() > 0) {
                foreach ($nodeChildren->getItems() as $child) {
                    array_push($attributeids, $child->getAttributeId());
                }
            }
        }
        
        $readresult = $this->_attributeManagerFactory->create()
            ->getCollection()
            ->addFieldToFilter('attribute_id', ['in' => $attributeids])
            ->addFieldToFilter('status', ['eq' => 1]);

        return $readresult;
    }

    /**
     * Get Catalog Resource Eav Attribute
     *
     * @param int $id
     * @return object
     */
    public function getCatalogResourceEavAttribute($id)
    {
        return $this->_attributeFactory->create()->load($id);
    }

    /**
     * Get Customer Group Collection
     *
     * @return object
     */
    public function getCustomerGroupCollection()
    {
        return $this->_groupModelFactory->create()->getCollection();
    }

    /**
     * Get Websites
     *
     * @return array
     */
    public function getWebsites()
    {
        if ($this->_websites !== null) {
            return $this->_websites;
        }

        $this->_websites = [
            0 => ['name' => __('All Websites'), 'currency' => $this->_directoryHelper->getBaseCurrencyCode()],
        ];
        $websites = $this->_storeManager->getWebsites();
        foreach ($websites as $website) {
            /** @var $website \Magento\Store\Model\Website */
            $this->_websites[$website->getId()] = [
                'name' => $website->getName(),
                'currency' => $website->getBaseCurrencyCode(),
            ];
        }
        return $this->_websites;
    }

    /**
     * Convert price to currency format
     *
     * @param float $price
     * @param mixed $toCurrency
     * @return float
     */
    public function convertCurrency($price, $toCurrency = null)
    {
        return $this->_currencyModel->convert($price, $toCurrency);
    }

    /**
     * Get Product Collection
     *
     * @param int $productId
     * @return object Magento\Catalog\Model\Product
     */
    public function getProductCollection($productId)
    {
        return $this->_productFactory->create()->load($productId);
    }

    /**
     * Get Ajax Check Url
     *
     * @return string
     */
    public function getAjaxCheckUrl()
    {
        return $this->getUrl('customattribute/product/changeset', ['_current' => true]);
    }
    
    /**
     * Get Mp Helper
     *
     * @return \Webkul\Marketplace\Helper\Data
     */
    public function getMpHelper()
    {
        return $this->_mpHelper;
    }
    
    /**
     * Get Json Helper
     *
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->_jsonHelper;
    }
    
    /**
     * Get Price Helper
     *
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function getPriceHelper()
    {
        return $this->_priceHelper;
    }

    /**
     * Get countries
     *
     * @return array
     */
    public function getCountries()
    {
        if (null === $this->_countries) {
            $this->_countries = $this->_sourceCountry->toOptionArray();
        }

        return $this->_countries;
    }
    /**
     * Check is multi website
     *
     * @return boolean
     */
    public function isMultiWebsites()
    {
        return !$this->_storeManager->hasSingleStore();
    }
    /**
     * Get directory helper
     *
     * @return \Magento\Directory\Helper\Data
     */
    public function getDirectoryHelper()
    {
        return $this->_directoryHelper;
    }
    /**
     * Get wysiwyg url
     *
     * @return string
     */
    public function getWysiwygUrl()
    {
        $currentTreePath = $this->wysiwygImages->idEncode(
            \Magento\Cms\Model\Wysiwyg\Config::IMAGE_DIRECTORY
        );
        $url =  $this->getUrl(
            'marketplace/wysiwyg_images/index',
            [
                'current_tree_path' => $currentTreePath
            ]
        );
        return $url;
    }
    /**
     * Format date
     *
     * @param string $date
     * @return string
     */
    public function getFormattedDate($date)
    {
        $dateTimeAsTimeZone = $this->timeZone->date(new \DateTime($date))->format('m/d/y H:i:s');
        return $dateTimeAsTimeZone;
    }
}
