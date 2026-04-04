<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model;

use Amasty\Mostviewed\Api\Data\GroupInterface;
use Amasty\Mostviewed\Model\OptionSource\BlockPosition;
use Amasty\Mostviewed\Model\ResourceModel\RuleIndex;
use Amasty\Mostviewed\Model\Rule\Condition\Combine as MostviewedCombine;
use Amasty\Mostviewed\Model\Rule\Condition\WhereCombine;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Rule\Model\Condition\Sql\Builder;
use Zend_Db_Select_Exception;

class Group extends AbstractGroup implements GroupInterface
{
    public const CACHE_TAG = 'mostviewed_group';

    public const FORM_NAME = 'amasty_mostviewed_product_group_form';

    public const PERSISTENT_NAME = 'amasty_mostviewed_rule';

    /**
     * Store rule combine conditions model
     *
     * @var \Magento\Rule\Model\Condition\Combine|null
     */
    private $whereConditions;

    /**
     * Store rule combine conditions model
     *
     * @var \Magento\Rule\Model\Condition\Combine|null
     */
    private $sameAsConditions;

    /**
     * @var MostviewedCombineFactory
     */
    private $combineFactory;

    /**
     * @var WhereCombineFactory
     */
    private $whereCombineFactory;

    /**
     * @var \Amasty\Mostviewed\Model\Rule\Condition\SameAsCombineFactory
     */
    private $sameAsCombineFactory;

    /**
     * @var \Magento\CatalogRule\Model\Rule\Condition\CombineFactory
     */
    private $actionFactory;

    /**
     * @var \Magento\Rule\Model\Condition\Combine
     */
    private $currentConditions;

    /**
     * @var \Amasty\Mostviewed\Model\Indexer\RuleProcessor
     */
    private $ruleProcessor;

    /**
     * @var Layout\Updater
     */
    private $layoutUpdater;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Status
     */
    private $stockResource;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var Builder
     */
    private $sqlBuilder;

    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Amasty\Mostviewed\Model\ResourceModel\Group::class);
        $this->setIdFieldName('group_id');
        $this->combineFactory = $this->getData('combineFactory');
        $this->whereCombineFactory = $this->getData('whereCombineFactory');
        $this->sameAsCombineFactory = $this->getData('sameAsCombineFactory');
        $this->actionFactory = $this->getData('actionFactory');
        $this->ruleProcessor = $this->getData('ruleProcessor');
        $this->layoutUpdater = $this->getData('layoutUpdater');
        $this->stockResource = $this->getData('stockResource');
        $this->moduleManager = $this->getData('moduleManager');
        $this->sqlBuilder = $this->getData('sqlBuilder');
        if ($this->getData('amastySerializer')) {
            $this->serializer = $this->getData('amastySerializer');
        }
    }

    /**
     * Getter for rule conditions collection. Product Conditions
     *
     * @return MostviewedCombine
     */
    public function getConditionsInstance(): MostviewedCombine
    {
        return $this->combineFactory->create();
    }

    /**
     * Getter for where to display rule conditions collection. Product Conditions
     *
     * @return WhereCombine
     */
    public function getWhereConditionsInstance(): WhereCombine
    {
        return $this->whereCombineFactory->create();
    }

    /**
     * Getter for rule conditions collection. Product Conditions
     *
     * @return \Magento\CatalogRule\Model\Rule\Condition\Combine
     */
    public function getSameAsConditionsInstance()
    {
        return $this->sameAsCombineFactory->create();
    }

    /**
     * Retrieve rule combine conditions model
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getWhereConditions()
    {
        if (empty($this->whereConditions)) {
            $this->_resetWhereConditions();
        }

        // Load rule conditions if it is applicable
        if ($this->hasWhereConditionsSerialized()) {
            $conditions = $this->getWhereConditionsSerialized();
            if (!empty($conditions)) {
                $conditions = $this->serializer->unserialize($conditions);
                if (is_array($conditions) && !empty($conditions)) {
                    $this->whereConditions->loadArray($conditions);
                }
            }
            $this->unsWhereConditionsSerialized();
        }

        return $this->whereConditions;
    }

    /**
     * Retrieve rule combine conditions model
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getSameAsConditions()
    {
        if (empty($this->sameAsConditions)) {
            $this->_resetSameAsConditions();
        }

        // Load rule conditions if it is applicable
        if ($this->hasSameAsConditionsSerialized()) {
            $conditions = $this->getSameAsConditionsSerialized();
            if (!empty($conditions)) {
                $conditions = $this->serializer->unserialize($conditions);
                if (is_array($conditions) && !empty($conditions)) {
                    $this->sameAsConditions->loadArray($conditions);
                }
            }
            $this->unsSameAsConditionsSerialized();
        }

        return $this->sameAsConditions;
    }

    /**
     * Reset rule combine conditions
     *
     * @param null|WhereCombine $conditions
     * @return void
     */
    protected function _resetWhereConditions(?WhereCombine $conditions = null): void
    {
        if (null === $conditions) {
            $conditions = $this->getWhereConditionsInstance();
        }

        $conditions->setRule($this)->setId('1')->setPrefix('conditions');
        $this->setWhereConditions($conditions);
    }

    /**
     * Reset rule combine conditions
     *
     * @param null|\Magento\Rule\Model\Condition\Combine $conditions
     *
     * @return $this
     */
    protected function _resetSameAsConditions($conditions = null)
    {
        if (null === $conditions) {
            $conditions = $this->getSameAsConditionsInstance();
        }
        $conditions->setRule($this)->setId('1')->setPrefix('conditions');
        $this->setSameAsConditions($conditions);

        return $this;
    }

    /**
     * Set rule combine conditions model
     *
     * @param \Magento\Rule\Model\Condition\Combine $conditions
     *
     * @return $this
     */
    public function setWhereConditions($conditions)
    {
        $this->whereConditions = $conditions;

        return $this;
    }

    /**
     * Set rule combine conditions model
     *
     * @param \Magento\Rule\Model\Condition\Combine $conditions
     *
     * @return $this
     */
    public function setSameAsConditions($conditions)
    {
        $this->sameAsConditions = $conditions;

        return $this;
    }

    /**
     * Initialize rule model data from array
     *
     * @param array $data
     *
     * @return $this
     */
    public function loadPost(array $data)
    {
        $arr = $this->_convertFlatToRecursive($data);
        if (isset($arr['conditions'])) {
            $this->getConditions()->setConditions([])->loadArray($arr['conditions'][1]);
        }
        if (isset($arr['where_conditions'])) {
            $this->getWhereConditions()
                ->setWhereConditions([])
                ->loadArray($arr['where_conditions'][1], 'where_conditions');
        }
        if (isset($arr['same_as_conditions'])) {
            $this->getSameAsConditions()
                ->setSameAsConditions([])
                ->loadArray($arr['same_as_conditions'][1], 'same_as_conditions');
        }

        return $this;
    }

    /**
     * Set specified data to current rule.
     * Set conditions and actions recursively.
     * Convert dates into \DateTime.
     *
     * @param array $data
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _convertFlatToRecursive(array $data)
    {
        $arr = [];
        foreach ($data as $key => $value) {
            if (in_array($key, ['conditions', 'where_conditions', 'same_as_conditions']) && is_array($value)) {
                foreach ($value as $id => $data) {
                    $path = explode('--', $id);
                    $node = &$arr;
                    for ($i = 0, $l = count($path); $i < $l; $i++) {
                        if (!isset($node[$key][$path[$i]])) {
                            $node[$key][$path[$i]] = [];
                        }
                        $node = &$node[$key][$path[$i]];
                    }
                    foreach ($data as $k => $v) {
                        $node[$k] = $v;
                    }
                }
            } else {
                /**
                 * Convert dates into \DateTime
                 */
                if (in_array($key, ['from_date', 'to_date'], true) && $value) {
                    $value = new \DateTime($value);
                }
                $this->setData($key, $value);
            }
        }

        return $arr;
    }

    /**
     * @return $this|AbstractGroup
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        parent::beforeSave();

        // Serialize conditions
        $where = $this->getWhereConditions();
        if ($where) {
            $this->setWhereConditionsSerialized($this->serializer->serialize($where->asArray()));
            $this->whereConditions = null;
        }

        // Serialize conditions
        $sameAs = $this->getSameAsConditions();
        if ($sameAs) {
            $this->setSameAsConditionsSerialized($this->serializer->serialize($sameAs->asArray()));
            $this->sameAsConditions = null;
        }

        if ($this->hasStores()) {
            $storeIds = $this->getStores();
            if (is_array($storeIds) && !empty($storeIds)) {
                $this->setStores(implode(',', $storeIds));
            }
        }

        if ($this->hasCustomerGroupIds()) {
            $groupIds = $this->getCustomerGroupIds();
            if (is_array($groupIds) && !empty($groupIds)) {
                $this->setCustomerGroupIds(implode(',', $groupIds));
            }
        }

        if ($this->hasCategoryIds()) {
            $categoryIds = $this->getCategoryIds();
            if (is_array($categoryIds) && !empty($categoryIds)) {
                $this->setCategoryIds(implode(',', $categoryIds));
            }
        }

        if (!$this->getGroupId()) {
            $this->setGroupId(null);
        }

        if ($this->shouldBeAddedToLayout()) {
            $this->layoutUpdater->execute(
                $this
            );
        } else {
            $this->layoutUpdater->delete($this->getLayoutUpdateId());
        }

        return $this;
    }

    /**
     * @return bool
     */
    private function shouldBeAddedToLayout()
    {
        return !in_array(
            $this->getBlockPosition(),
            [
                BlockPosition::PRODUCT_INTO_UPSELL,
                BlockPosition::CART_INTO_CROSSSEL,
                BlockPosition::PRODUCT_INTO_RELATED,
                BlockPosition::CUSTOM
            ]
        );
    }

    /**
     * Always recalculate matchedProducts to prevent indexing only the first matched products.
     * @see \Amasty\Mostviewed\Model\Indexer\ProductIndexer::doReindex()
     *
     * @return array|null
     * phpcs:disable Generic.Metrics.NestingLevel.TooHigh
     */
    public function getMatchingProductIdsByGroup(): ?array
    {
        $relation = $this->getRelation();
        $matchedProducts = [];
        $this->setCollectedAttributes([]);
        $stores = explode(',', $this->getStores());
        if (in_array(0, $stores)) {
            $allStores = $this->_storeManager->getStores();
            $stores = [];
            foreach ($allStores as $store) {
                $stores[] = $store->getId();
            }
        }
        foreach ($stores as $storeId) {
            switch ($relation) {
                case RuleIndex::WHAT_SHOW:
                    $this->currentConditions = $this->getConditions();
                    if (!$this->currentConditions->getConditions()) {
                        $this->currentConditions = null; // all products- don't apply
                    }
                    break;
                case RuleIndex::WHERE_SHOW:
                    if ($this->getCategoryIds()) {
                        foreach (explode(',', $this->getCategoryIds()) as $categoryId) {
                            $matchedProducts['categories'][$categoryId][] = $storeId;
                        }
                    } else {
                        $this->currentConditions = $this->getWhereConditions();
                    }
                    break;
            }
            if ($this->currentConditions) {
                $matchedProducts = array_replace_recursive($matchedProducts, $this->collectProductsByConditions(
                    $storeId,
                    $relation
                ));
            } else {
                if (!$this->getCategoryIds()) {
                    $matchedProducts = null;
                }
            }
        }

        return $matchedProducts;
    }

    /**
     * @param Collection $collection
     * @param Product $product
     */
    public function applySameAsConditions(Collection $collection, Product $product)
    {
        $conditions = [];
        $combineConditions = $this->getSameAsConditions();
        if ($combineConditions && is_array($combineConditions->getData('same_as_conditions'))) {
            $conditions = $combineConditions->getData('same_as_conditions');
        }
        if (!empty($conditions)) {
            $appliedCondition = 0;
            foreach ($conditions as $sameAsCondition) {
                if (method_exists($sameAsCondition, 'apply')) {
                    if ($sameAsCondition->apply($collection, $product, !$combineConditions->getValue())) {
                        $appliedCondition++;
                    }
                }
            }
            if ($combineConditions->getAggregator() == 'any' && $appliedCondition) {
                $this->changeAggregator($collection, $appliedCondition);
            }
        }
    }

    /**
     * Change AND operator to OR
     *
     * @param $collection
     * @param $appliedConditions
     */
    private function changeAggregator($collection, $appliedConditions)
    {
        $where = $collection->getSelect()->getPart(Select::WHERE);
        $sameConditions = array_slice($where, -1 * $appliedConditions, null, true);
        $sameWhere = '';
        $andRegexp = '@' . Select::SQL_AND . '@';
        foreach ($sameConditions as $key => $sameCondition) {
            if (empty($sameWhere)) {
                if (count($sameConditions) != count($where)) {
                    $sameWhere .= ' ' . Select::SQL_AND;
                }
                $sameWhere .= ' (' .
                    preg_replace($andRegexp, '', $sameCondition, 1);
            } else {
                $sameWhere .= ' ' . preg_replace($andRegexp, Select::SQL_OR, $sameCondition, 1);
            }
            unset($where[$key]);
        }

        if ($sameWhere) {
            $sameWhere .= ')';
            $where[] = $sameWhere;
            $collection->getSelect()->setPart(Select::WHERE, $where);
        }
    }

    /**
     * @param $storeId
     * @param $relation
     */
    private function collectProductsByConditions($storeId, $relation): array
    {
        /** @var $productCollection Collection */
        $productCollection = $this->_productCollectionFactory->create()
            ->setStoreId($storeId);

        if ($this->_productsFilter) {
            $productCollection->addIdFilter($this->_productsFilter);
        }
        if ($relation == RuleIndex::WHERE_SHOW && $this->getShowForOutOfStock()) {
            $this->addStockFilter($productCollection, $storeId);
        }

        $this->currentConditions->collectValidatedAttributes($productCollection);
        $this->sqlBuilder->attachConditionToCollection($productCollection, $this->currentConditions);

        $matchedProducts = [];
        foreach ($productCollection->getAllIds() as $productId) {
            $matchedProducts[$productId][$storeId] = $storeId;
        }

        return $matchedProducts;
    }

    /**
     * @param Collection $productCollection
     * @param int $storeId
     */
    private function addStockFilter(Collection $productCollection, $storeId)
    {
        try {
            $this->stockResource->addStockStatusToSelect(
                $productCollection->getSelect(),
                $this->_storeManager->getStore($storeId)->getWebsite()
            );
            $fromTables = $productCollection->getSelect()->getPart('from');
            if ($this->moduleManager->isEnabled('Magento_Inventory')
                && $fromTables['stock_status']['tableName']
                != $productCollection->getResource()->getTable('cataloginventory_stock_status')
            ) {
                $salableColumn = 'is_salable';
            } else {
                $salableColumn = 'stock_status';
                $fromTables['stock_status']['joinCondition'] = preg_replace(
                    '@(stock_status.website_id=)\d+@',
                    '$1 0',
                    $fromTables['stock_status']['joinCondition']
                );
                $productCollection->getSelect()->setPart(Select::FROM, $fromTables);
            }
            $productCollection->getSelect()->where('stock_status.' . $salableColumn . ' = 0');
        } catch (NoSuchEntityException $e) {
            $this->logError($e->getMessage());
        } catch (Zend_Db_Select_Exception $e) {
            $this->logError($e->getMessage());
        }
    }

    /**
     * @param string $message
     */
    private function logError(string $message)
    {
        $this->_logger->error($message);
    }

    /**
     * @param string $action
     * @param float|int|string $discount
     *
     * @return array
     */
    public function validateDiscount($action, $discount)
    {
        return [];
    }

    /**
     * @return $this
     */
    public function afterSave()
    {
        if (!$this->ruleProcessor->isIndexerScheduled()) {
            $this->getResource()->addCommitCallback([$this, 'reindex']);
        }

        return $this;
    }

    /**
     *
     */
    public function reindex()
    {
        $this->ruleProcessor->reindexRow($this->getId());
    }

    /**
     * @param string $formName
     *
     * @return string
     */
    public function getConditionsFieldSetId($formName = '')
    {
        return $formName . '_product_group_conditions_fieldset_' . $this->getId();
    }

    /**
     * @param string $formName
     *
     * @return string
     */
    public function getWhereConditionsFieldSetId($formName = '')
    {
        return $formName . '_product_group_where_conditions_fieldset_' . $this->getId();
    }

    /**
     * @param string $formName
     *
     * @return string
     */
    public function getSameAsConditionsFieldSetId($formName = '')
    {
        return $formName . '_product_group_same_as_conditions_fieldset_' . $this->getId();
    }
}
