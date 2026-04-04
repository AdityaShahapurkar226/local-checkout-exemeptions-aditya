<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel;

use Amasty\Mostviewed\Model\Pack\Store\Table;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Pack extends AbstractDb
{
    public const PACK_PRODUCT_TABLE = 'amasty_mostviewed_pack_product';

    public const PACK_TABLE = 'amasty_mostviewed_pack';

    /**
     * @var array
     */
    private $savedData = [];

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::PACK_TABLE, 'pack_id');
    }

    /**
     * @param AbstractModel $object
     * @return AbstractDb
     */
    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId()) {
            $object->setData(
                'parent_ids',
                $this->getParentIdsByPack($object->getId())
            );
        }

        return parent::_afterLoad($object);
    }

    /**
     * @param $packId
     * @return array
     */
    public function getParentIdsByPack($packId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable(self::PACK_PRODUCT_TABLE),
            ['product_id']
        )->where('pack_id = ?', $packId);

        return $this->getConnection()->fetchCol($select, []);
    }

    /**
     * @param array $productIds
     * @param int $storeId
     *
     * @return array
     */
    public function getIdsByProductsAndStore($productIds, $storeId)
    {
        $select = $this->getConnection()->select()->from(
            ['pack' => $this->getTable(self::PACK_PRODUCT_TABLE)],
            ['pack_id']
        )->join(
            ['pack_store' => $this->getTable(Table::NAME)],
            'pack.pack_id = pack_store.pack_id'
        )->where(
            'product_id IN(?)',
            $productIds
        )->where(
            'pack_store.store_id IN(?)',
            [0, $storeId]
        );

        return $this->getConnection()->fetchCol($select, []);
    }

    /**
     * @param array $productIds
     * @param int $storeId
     *
     * @return array
     */
    public function getIdsByChildProductsAndStore($productIds, $storeId)
    {
        $select = $this->getConnection()->select()->from(
            ['main_table' => $this->getMainTable()],
            ['pack_id']
        )->join(
            ['pack_store' => $this->getTable(Table::NAME)],
            'main_table.pack_id = pack_store.pack_id'
        )->where('pack_store.store_id IN (?)', [0, $storeId]);

        if ($productIds) {
            $query = '';
            foreach ($productIds as $productId) {
                $query .= " OR " . "CONCAT(',', product_ids, ',')" . " LIKE '%," . (int)$productId . ",%'";
            }
            $query = trim($query, ' OR');
            $select->where($query);
        }

        return $this->getConnection()->fetchCol($select, []);
    }

    public function getIdsByStore(int $storeId): array
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable(Table::NAME),
            [Table::PACK_COLUMN]
        )->where(sprintf('%s IN (?)', Table::STORE_COLUMN), [0, $storeId]);

        return array_unique($this->getConnection()->fetchCol($select));
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $this->savedData = $object->getData();

        return parent::_beforeSave($object);
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        if (isset($this->savedData['parent_product_ids'])
            && $this->savedData['parent_product_ids']
        ) {
            $this->deletePackAdditional($object);
            $this->savePackProductData(
                [
                    'parent_product_ids' => $this->savedData['parent_product_ids'],
                    'pack_id'            => $object->getId()
                ]
            );
        }

        return parent::_afterSave($object);
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this
     */
    protected function _afterDelete(AbstractModel $object)
    {
        $this->deletePackAdditional($object);

        return parent::_afterDelete($object);
    }

    /**
     * @param array $data
     */
    private function savePackProductData($data)
    {
        $insertData = [];
        foreach ($data['parent_product_ids'] as $parentProductId) {
            $insertData[] = [
                'pack_id'    => $data['pack_id'],
                'product_id' => $parentProductId,
            ];
        }
        if ($insertData) {
            $this->getConnection()->insertOnDuplicate(
                $this->getTable(self::PACK_PRODUCT_TABLE),
                $insertData
            );
        }
    }

    /**
     * @param AbstractModel $object
     */
    private function deletePackAdditional(AbstractModel $object)
    {
        if ($object->getPackId()) {
            $this->getConnection()->delete(
                $this->getTable(self::PACK_PRODUCT_TABLE),
                ['pack_id=?' => $object->getPackId()]
            );
        }
    }
}
