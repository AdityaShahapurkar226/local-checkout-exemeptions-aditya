<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceBaseShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceBaseShipping\Model\ShippingSetting;

use Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface;
use Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterfaceFactory;
use Webkul\MarketplaceBaseShipping\Model\ResourceModel\ShippingSetting as ResourceShippingSetting;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * Customer data model
 *
 */
class AbstractShippingSetting extends AbstractExtensibleModel implements ShippingSettingInterface, IdentityInterface
{
    /**
     * Customer data cache tag
     */
    const CACHE_TAG = 'base_shipping';

    /**#@-*/
    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'base_shipping_setting';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        ShippingSettingInterfaceFactory $shippingSettingDataFactory,
        RegionInterfaceFactory $regionDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_directoryData = $directoryData;
        //$data = $this->_implodeArrayField($data);
        $this->_regionFactory = $regionFactory;
        $this->_countryFactory = $countryFactory;
        $this->shippingSettingDataFactory = $shippingSettingDataFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

     /**
      * Retrieve region name
      *
      * @return string
      */
    public function getRegion()
    {
        $regionId = $this->getData('region_id');
        $region = $this->getData('region');

        if (!$regionId && is_numeric($region)) {
            if ($this->getRegionDataModel($region)->getCountryId() == $this->getCountryId()) {
                $this->setRegion($this->getRegionDataModel($region)->getName());
                $this->setRegionId($region);
            }
        } elseif ($regionId) {
            if ($this->getRegionDataModel($regionId)->getCountryId() == $this->getCountryId()) {
                $this->setRegion($this->getRegionDataModel($regionId)->getName());
            }
        } elseif (is_string($region)) {
            $this->setRegion($region);
        }
        
        return $this->getData('region');
    }

    /**
     * Return 2 letter state code if available, otherwise full region name
     *
     * @return string
     */
    public function getRegionCode()
    {
        $regionId = $this->getData('region_id');
        $region = $this->getData('region');

        if (!$regionId && is_numeric($region)) {
            if ($this->getRegionDataModel($region)->getCountryId() == $this->getCountryId()) {
                $this->setData('region_code', $this->getRegionDataModel($region)->getCode());
            }
        } elseif ($regionId) {
            if ($this->getRegionDataModel($regionId)->getCountryId() == $this->getCountryId()) {
                $this->setRegionCode($this->getRegionDataModel($regionId)->getCode());
            }
        } elseif (is_string($region)) {
            $this->setRegionCode($region);
        }
        return $this->getData('region_code');
    }

    /**
     * @return int
     */
    public function getRegionId()
    {
        $regionId = $this->getData('region_id');
        $region = $this->getData('region');
        if (!$regionId) {
            if (is_numeric($region)) {
                $this->setData('region_id', $region);
                $this->unsRegion();
            } else {
                $regionModel = $this->_regionFactory->create()->loadByCode(
                    $this->getRegionCode(),
                    $this->getCountryId()
                );
                $this->setData('region_id', $regionModel->getId());
            }
        }
        return $this->getData('region_id');
    }
    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @param int $entityId
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @return int
     */
    public function getSellerId()
    {
        return $this->getData(self::SELLER_ID);
    }

    /**
     * Set seller ID
     * @param int $sellerId
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * @return string|null
     */
    public function getCompany()
    {
        return $this->getData(self::COMPANY);
    }

    /**
     * @param int $company
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setCompany($company)
    {
        return $this->setData(self::COMPANY, $company);
    }
    
    /**
     * @return string|null
     */
    public function getTelephone()
    {
        return $this->getData(self::TELEPHONE);
    }

    /**
     * @param int $telephone
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setTelephone($telephone)
    {
        return $this->setData(self::TELEPHONE, $telephone);
    }

    /**
     * @return string|null
     */
    public function getCountryId()
    {
        return $this->getData(self::COUNTRY_ID);
    }

    /**
     * @param int $countryCode
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setCountryId($countryCode)
    {
        return $this->setData(self::COUNTRY_ID, $countryCode);
    }

    /**
     * @param int $region
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setRegion($region)
    {
        return $this->setData(self::REGION, $region);
    }
    /**
     * @param int $regionCode
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setRegionId($regionCode)
    {
        return $this->setData(self::REGION_ID, $regionCode);
    }

    /**
     * @return string|null
     */
    public function getPostalCode()
    {
        return $this->getData(self::POSTAL_CODE);
    }

    /**
     * @param int $postalCode
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setPostalCode($postalCode)
    {
        return $this->setData(self::POSTAL_CODE, $postalCode);
    }

    /**
     * @return string|null
     */
    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
     * @param int $city
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * @return string|null
     */
    public function getStreet()
    {
        $serializer = ObjectManager::getInstance()->get(Json::class);
        if ($this->getData(self::STREET)) {
            return $serializer->unserialize($this->getData(self::STREET), true);
        }
        return $this->getData(self::STREET);
    }
    /**
     * @return string|null
     */
    public function getStreet1()
    {
        $street = $this->getStreet();
        if (isset($street[0])) {
            return $street[0];
        }
        return '';
    }

    /**
     * @return string|null
     */
    public function getStreet2()
    {
        $street = $this->getStreet();
        if (isset($street[1])) {
            return $street[1];
        }
        return '';
    }

    /**
     * @param int $street
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setStreet($street)
    {
        $serializer = ObjectManager::getInstance()->get(Json::class);
        return $this->setData(self::STREET, $serializer->serialize($street));
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * @param int $storeId
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * Retrieve country model
     *
     * @param int|null $regionId
     * @return \Magento\Directory\Model\Region
     */
    public function getRegionDataModel($regionId = null)
    {
        if ($regionId === null) {
            $regionId = $this->getRegionId();
        }

        $region = $this->_regionFactory->create();
        $region->load($regionId);

        return $region;
    }
}
