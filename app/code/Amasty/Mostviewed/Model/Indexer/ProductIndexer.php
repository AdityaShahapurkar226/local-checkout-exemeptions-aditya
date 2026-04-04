<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Indexer;

use Amasty\Mostviewed\Model\ResourceModel\RuleIndex;
use Amasty\Mostviewed\Model\Group as Rule;

class ProductIndexer extends AbstractIndexer
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

            /** @var Rule $rule */
            foreach ($this->getRules()->getItems() as $rule) {
                $rule->setRelation($relation);
                $ruleId = $rule->getGroupId();
                $position = $rule->getBlockPosition();
                $rule->setProductsFilter($ids);
                $matchedProducts = $rule->getMatchingProductIdsByGroup() ?: [];
                if (isset($matchedProducts['categories'])) {
                    continue;
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
                        if (++$count > 1000) {
                            $this->getIndexResource()->insertIndexData($rows);
                            $count = 0;
                            $rows = [];
                        }
                    }
                }
                $this->registerEntities(Rule::CACHE_TAG, [$ruleId]);
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
        $this->getIndexResource()->cleanByProductIds($ids, $relation);
    }
}
