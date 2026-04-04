<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MarketplaceBaseShipping
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\MarketplaceBaseShipping\Model\ShippingSetting;

use Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface;

/**
 * Class Mapper converts ShippingSetting Service Data Object to an array
 */
class Mapper
{
    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        \Magento\Framework\Api\ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * Convert address data object to a flat array
     *
     * @param ShippingSettingInterface $shippingDataObject
     * @return array
     * TODO:: Add concrete type of ShippingSettingInterface for $shippingDataObject parameter once
     * all references have been refactored.
     */
    public function toFlatArray($shippingDataObject)
    {
        $flatDataArray = $this->extensibleDataObjectConverter->toFlatArray(
            $shippingDataObject,
            [],
            \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface::class
        );
        //preserve street
        $street = $shippingDataObject->getStreet();
        if (!empty($street) && is_array($street)) {
            // Unset flat street data
            $streetKeys = array_keys($street);
            foreach ($streetKeys as $key) {
                unset($flatDataArray[$key]);
            }
            //Restore street as an array
            $flatDataArray[ShippingSettingInterface::STREET] = $street;
        }
        return $flatDataArray;
    }
}
