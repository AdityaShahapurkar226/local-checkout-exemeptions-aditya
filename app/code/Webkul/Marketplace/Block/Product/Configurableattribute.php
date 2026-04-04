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

namespace Webkul\Marketplace\Block\Product;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Catalog\Model\Product;

/**
 * Webkul Marketplace Product Configurableattribute Block.
 */
class Configurableattribute extends \Magento\Framework\View\Element\Template
{
    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute
     * @param AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $eavAttribute,
        AttributeRepositoryInterface $attributeRepository,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->eavAttribute = $eavAttribute;
        $this->attributeRepository = $attributeRepository;
        $this->_attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }
    /**
     * Return attribute data
     *
     * @param int $attributeId
     * @return void
     */
    public function getEditAttributeData($attributeId)
    {
        $storeOptions = [];
        $attribute = $this->eavAttribute->load($attributeId);
        $attributeCode = $attribute->getAttributeCode();
        $attributeDetails = $this->attributeRepository->get(Product::ENTITY, $attributeCode);
        $attributeDetails->setStoreId(1);
        $options = $attributeDetails->getSource()->getAllOptions();
        foreach ($options as $data) {
            if (empty($data['value'])) {
                continue;
            }
            $storeOptions[$data['value']] = $data['label'];
        }
        $configOutputData['attribute_code'] =  $attributeCode;
        $configOutputData['attribute_label'] = $attributeDetails->getFrontendLabel();
        $configOutputData['frontend_input'] = $attributeDetails->getFrontendInput();
        $configOutputData['val_required'] =  $attributeDetails->getIsRequired();
        $configOutputData['default_value'] = $attributeDetails->getDefaultValue();
        $configOutputData['store_options'] = $storeOptions;
        $configOutputData['options'] = $this->getOptionsData($attribute->getId(), $attributeDetails->getDefaultValue());
       
        return $configOutputData;
    }

    /**
     * Return options data
     *
     * @param int $attributeId
     * @param mixed $defaultValue
     * @return array
     */
    public function getOptionsData($attributeId, $defaultValue)
    {
        $output = [];
        $valuesData = $this->_attrOptionCollectionFactory->create()->setAttributeFilter(
            $attributeId
        )->setPositionOrder(
            'asc',
            true
        )->load();
        return $valuesData;
    }

    /**
     * Get Json Helper Data
     *
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
}
