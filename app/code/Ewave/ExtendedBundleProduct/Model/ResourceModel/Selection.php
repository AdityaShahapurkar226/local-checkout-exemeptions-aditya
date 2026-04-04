<?php

namespace Ewave\ExtendedBundleProduct\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;

class Selection extends \Magento\Bundle\Model\ResourceModel\Selection
{
    /**
     * @param int $selectionId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getParentProductIdBySelectionId($selectionId)
    {
        $connection = $this->getConnection();
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $select = $connection->select()->distinct(
            true
        )->from(
            $this->getMainTable(),
            ''
        )->join(
            ['e' => $this->metadataPool->getMetadata(ProductInterface::class)->getEntityTable()],
            'e.' . $metadata->getLinkField() . ' = ' .  $this->getMainTable() . '.parent_product_id',
            ['e.entity_id as parent_product_id']
        )->where(
            $this->getMainTable() . '.selection_id = ?',
            $selectionId
        );

        return $connection->fetchOne($select);
    }
}
