<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel;

use Amasty\Mostviewed\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Catalog\Model\Product as ProductModel;
use Amasty\Mostviewed\Model\OptionSource\BlockPosition;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;

class RuleIndex extends AbstractDb
{
    public const MAIN_TABLE = 'amasty_mostviewed_product_index';

    // columns
    public const INDEX_ID = 'index_id';

    public const RULE_ID = 'rule_id';

    public const ENTITY_ID = 'entity_id';

    public const RELATION = 'relation';

    public const STORE_ID = 'store_id';

    public const POSITION = 'position';

    // relations
    public const WHERE_SHOW = 'where_show';

    public const WHAT_SHOW = 'what_show';

    /**
     * @var array
     */
    private $categoryPositions = [];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(StoreManagerInterface $storeManager, Context $context)
    {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, 'index_id');
        $this->categoryPositions = [
            BlockPosition::CATEGORY_CONTENT_BOTTOM,
            BlockPosition::CATEGORY_CONTENT_TOP,
            BlockPosition::CATEGORY_SIDEBAR_BOTTOM,
            BlockPosition::CATEGORY_SIDEBAR_TOP
        ];
    }

    /**
     * @param int $entityId
     * @param string $position
     *
     * @return array
     */
    public function getGroupByIdAndPosition($entityId, $position)
    {
        $sql = $this->getConnection()->select()->from($this->getMainTable(), 'rule_id')
            ->where('entity_id = ?', $entityId)
            ->where('relation = ?', self::WHERE_SHOW)
            ->where('store_id = ?', $this->storeManager->getStore()->getId());

        if (!empty($position)) {
            $sql->where('position = ?', $position);
        }

        return $this->getConnection()->fetchAll($sql);
    }

    /**
     * @param ProductCollection $collection
     * @param $ruleId
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function applyProductsFilterToCollection(ProductCollection $collection, $ruleId)
    {
        $collection->getSelect()->join(
            ['ampi' => $this->getMainTable()],
            sprintf(
                'e.entity_id = ampi.entity_id AND ampi.rule_id=%s AND ampi.relation="%s" AND ampi.store_id=%s',
                (int)$ruleId,
                self::WHAT_SHOW,
                (int)$this->storeManager->getStore()->getId()
            ),
            []
        );
        $collection->getSelect()->group('e.entity_id');

        return $this;
    }

    /**
     * @param string $relation
     *
     * @return $this
     */
    public function cleanAllIndex($relation)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            [
                self::RELATION . ' = ?' => $relation
            ]
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function cleanEmptyData()
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            [
                self::RELATION . ' IS NULL OR ' . self::RELATION . '=""'
            ]
        );

        return $this;
    }

    /**
     * @param array $ruleIds
     * @param string $relation
     *
     * @return $this
     */
    public function cleanByRuleIds($ruleIds, $relation)
    {
        return $this->clean(self::RULE_ID, $ruleIds, $relation);
    }

    /**
     * @param array $entityIds
     * @param string $relation
     *
     * @return $this
     */
    public function cleanByProductIds($entityIds, $relation)
    {
        $additionalConditions = [
            self::POSITION . ' NOT IN (?)' => $this->categoryPositions
        ];

        return $this->clean(self::ENTITY_ID, $entityIds, $relation, $additionalConditions);
    }

    /**
     * @param string $field
     * @param array $values
     * @param string $relation
     * @param array $additionalConditions
     *
     * @return $this
     */
    private function clean($field, $values, $relation, $additionalConditions = [])
    {
        $condition = array_merge(
            [
                $field . ' IN (?)'      => $values,
                self::RELATION . ' = ?' => $relation
            ],
            $additionalConditions
        );
        $this->getConnection()->delete(
            $this->getMainTable(),
            $condition
        );

        return $this;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function insertIndexData(array $data)
    {
        $this->getConnection()->insertOnDuplicate($this->getMainTable(), $data);

        return $this;
    }

    /**
     * @return string
     */
    public function getFullTableName()
    {
        return $this->getTable(self::MAIN_TABLE);
    }

    /**
     * @return array
     */
    public function getCategoryPositions()
    {
        return $this->categoryPositions;
    }
}
