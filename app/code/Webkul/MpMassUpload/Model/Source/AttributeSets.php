<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpMassUpload\Model\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class AttributeSets implements OptionSourceInterface
{

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $_entity;

    /**
     * @var Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    protected $_setCollection;

    /**
     * Constructor
     *
     * @param CollectionFactory         $setCollection
     * @param \Magento\Eav\Model\Entity $entity
     * @param array                     $data
     */
    public function __construct(
        CollectionFactory $setCollection,
        \Magento\Eav\Model\Entity $entity,
        array $data = []
    ) {
        $this->_entity = $entity;
        $this->_setCollection = $setCollection;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attributeSetArr = [];
        $attributeSetCollection = $this->getAttributeSetCollection();
        foreach ($attributeSetCollection as $attribute) {
            $attributeSetArr[] = [
                'value' => $attribute->getAttributeSetId(),
                'label'=> $attribute->getAttributeSetName()
            ];
        }
        return $attributeSetArr;
    }

    /**
     * Get Attribute Set Collection.
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory
     */
    public function getAttributeSetCollection()
    {
        $entityTypeId = $this->_entity
              ->setType('catalog_product')
              ->getTypeId();
        $attributeSetCollection = $this->_setCollection
              ->create()
              ->setEntityTypeFilter($entityTypeId);
        return $attributeSetCollection;
    }
}
