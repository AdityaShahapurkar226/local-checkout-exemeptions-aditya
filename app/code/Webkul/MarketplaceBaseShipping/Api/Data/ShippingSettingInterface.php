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
namespace Webkul\MarketplaceBaseShipping\Api\Data;

/**
 * MarketplaceBaseShipping blocked customer interface.
 * @api
 */
interface ShippingSettingInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID             = 'entity_id';
    const SELLER_ID             = 'seller_id';
    const COMPANY               = 'company';
    const TELEPHONE             = 'telephone';
    const COUNTRY_ID            = 'country_id';
    const REGION                = 'region';
    const REGION_ID             = 'region_id';
    const POSTAL_CODE           = 'postal_code';
    const CITY                  = 'city';
    const STREET                = 'street';
    const STORE_ID              = 'store_id';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $entityId
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setId($entityId);

    /**
     * @return int
     */
    public function getSellerId();

    /**
     * Set seller ID
     * @param int $sellerId
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setSellerId($sellerId);
    
    /**
     * @return string|null
     */
    public function getCompany();

    /**
     * @param int $company
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setCompany($company);
    
    /**
     * @return string|null
     */
    public function getTelephone();

    /**
     * @param int $telephone
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setTelephone($telephone);

    /**
     * @return string|null
     */
    public function getCountryId();

    /**
     * @param int $countryCode
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setCountryId($countryCode);

    /**
     * @return string|null
     */
    public function getRegion();

    /**
     * @param int $region
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setRegion($region);

    /**
     * @return string|null
     */
    public function getRegionCode();

    /**
     * @param int $regionCode
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setRegionId($regionCode);

    /**
     * @return string|null
     */
    public function getPostalCode();

    /**
     * @param int $postalCode
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setPostalCode($postalCode);

    /**
     * @return string|null
     */
    public function getCity();

    /**
     * @param int $city
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setCity($city);

    /**
     * @return string|null
     */
    public function getStreet();

    /**
     * @param int $street
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setStreet($street);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $storeId
     * @return \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface
     */
    public function setStoreId($storeId);
}
