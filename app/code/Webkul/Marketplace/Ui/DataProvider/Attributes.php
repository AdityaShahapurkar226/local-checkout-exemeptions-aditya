<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Marketplace
 * @author Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Ui\DataProvider;

use Webkul\Marketplace\Model\ResourceModel\VendorAttributeMapping\CollectionFactory as VendorMappingCollection;
use Webkul\Marketplace\Helper\Data as HelperData;

class Attributes extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected $collection;

    /**
     * @var \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler
     */
    private $configurableAttributeHandler;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $configurableAttributeHandler
     * @param VendorMappingCollection $mappingColl
     * @param HelperData $helperData
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $configurableAttributeHandler,
        VendorMappingCollection $mappingColl,
        HelperData $helperData,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->configurableAttributeHandler = $configurableAttributeHandler;
        $this->mappingColl = $mappingColl;
        $this->helperData = $helperData;
        $this->collection = $configurableAttributeHandler->getApplicableAttributes();
    }

    /**
     * Returns attribute collection
     *
     * @return void
     */
    public function getCollection()
    {
        return $this->collection;
    }

   /**
    * Return allowed items
    *
    * @return array
    */
    public function getData()
    {
        $allowedAttributes = [];
        $sellerId = $this->helperData->getCustomerId();
        $items = [];
        $skippedItems = 0;
        foreach ($this->getCollection()->getItems() as $attribute) {
            if ($this->configurableAttributeHandler->isAttributeApplicable($attribute)) {
                $isLoginVendorAttribute = $this->isVendorAttribute($attribute->getId(), $sellerId);
                if (in_array($attribute->getId(), $isLoginVendorAttribute)) {
                    $items[] = $attribute->toArray();
                } else {
                    $skippedItems++;
                }
            } else {
                $skippedItems++;
            }
        }
        
        return [
            'totalRecords' => $this->collection->getSize() - $skippedItems,
            'items' => $items
        ];
    }

    /**
     * Return allowed attribute
     *
     * @param int $attributeId
     * @param int $sellerId
     * @return array
     */
    private function isVendorAttribute($attributeId, $sellerId)
    {
        $output = [];
        $mappingCollection = $this->mappingColl->create()
        ->addFieldToFilter('attribute_id', $attributeId);
        foreach ($mappingCollection as $data) {
            if ($data->getSellerId() == $sellerId) {
                array_push($output, $data->getAttributeId());
            } elseif (empty($sellerId)) {
                array_push($output, $data->getAttributeId());
            } else {
                return $output;
            }
        }
        array_push($output, $attributeId);
        return $output;
    }
}
