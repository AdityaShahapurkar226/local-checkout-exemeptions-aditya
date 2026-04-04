<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Indexer;

use Amasty\Mostviewed\Model\ResourceModel\RuleIndex;
use Amasty\Mostviewed\Model\Group;
use Magento\Catalog\Model\Product;
use Amasty\Mostviewed\Block\Widget\Related;

class RuleIndexer extends AbstractIndexer
{
    /**
     * @inheritdoc
     */
    protected function doReindex($ids = [])
    {
        $rows = [];
        $count = 0;
        foreach ([RuleIndex::WHERE_SHOW, RuleIndex::WHAT_SHOW] as $relation) {
            $this->clean($relation, $ids);

            /** @var Group $rule */
            foreach ($this->getRules($ids)->getItems() as $rule) {
                $rule->setRelation($relation);
                $ruleId = $rule->getGroupId();
                $position = $rule->getBlockPosition();
                $matchedProducts = $rule->getMatchingProductIdsByGroup() ?: [];
                if (isset($matchedProducts['categories'])) {
                    $matchedProducts = $matchedProducts['categories'];
                }
                foreach ($matchedProducts as $productId => $storeIds) {
                    while ($storeIds) {
                        $rows[] = [
                            RuleIndex::ENTITY_ID => $productId,
                            RuleIndex::RELATION  => $relation,
                            RuleIndex::STORE_ID  => array_shift($storeIds),
                            RuleIndex::RULE_ID   => $ruleId,
                            RuleIndex::POSITION  => $position
                        ];
                        if (++$count > $this->batchCount) {
                            $this->getIndexResource()->insertIndexData($rows);
                            $count = 0;
                            $rows = [];
                        }
                    }
                    if ($relation == RuleIndex::WHERE_SHOW) {
                        $this->registerEntities(Product::CACHE_TAG, [$productId]);
                    }
                }
                $this->registerEntities(Group::CACHE_TAG, [$ruleId]);
                $this->registerEntities(Related::CACHE_TAG, [$position]);
            }
        }
        $this->cleanCache();

        if (!empty($rows)) {
            $this->getIndexResource()->insertIndexData($rows);
        }
    }

    /**
     * @inheritdoc
     */
    protected function cleanList($relation, $ids)
    {
        $this->getIndexResource()->cleanByRuleIds($ids, $relation);
    }
}
