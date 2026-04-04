<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Rule\Condition;

use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Combine;
use Magento\Rule\Model\Condition\Sql\ExpressionFactory;

class SqlBuilder extends \Magento\Rule\Model\Condition\Sql\Builder
{
    /**
     * @var array
     */
    private $stringConditionOperatorMap = [
        '{}' => ':field LIKE ?',
        '!{}' => ':field NOT LIKE ?',
    ];

    /**
     * @var AbstractCollection|null
     */
    private $currentCollection;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    public function __construct(
        ExpressionFactory $expressionFactory,
        AttributeRepositoryInterface $attributeRepository
    ) {
        parent::__construct($expressionFactory, $attributeRepository);
        $this->attributeRepository = $attributeRepository;
    }

    public function attachConditionToCollection(
        AbstractCollection $collection,
        Combine $combine
    ): void {
        $this->currentCollection = $collection;
        parent::attachConditionToCollection($collection, $combine);
        $this->currentCollection = null;
    }

    /**
     * Overridden to allow filter by default value
     *
     * @param AbstractCondition $condition
     * @param string $value
     * @param bool $isDefaultStoreUsed
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getMappedSqlCondition(
        AbstractCondition $condition,
        string $value = '',
        bool $isDefaultStoreUsed = true
    ): string {
        // fix default scope usage
        $isDefaultStoreUsed = $this->checkIsDefaultStoreUsed($this->currentCollection);
        $argument = $condition->getMappedSqlField();

        // If rule hasn't valid argument - prevent incorrect rule behavior.
        if (empty($argument)) {
            return (string) $this->_expressionFactory->create(['expression' => '1 = -1']);
        } elseif (preg_match('/[^a-z0-9\-_\.\`]/i', $argument) > 0) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Invalid field'));
        }

        $conditionOperator = $condition->getOperatorForValidate();

        if (!isset($this->_conditionOperatorMap[$conditionOperator])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unknown condition operator'));
        }

        $defaultValue = 0;
        // Check if attribute has a table with default value and add it to the query
        if ($condition->getAttribute()
            && $this->canAttributeHaveDefaultValue($condition->getAttribute(), $isDefaultStoreUsed)
        ) {
            $defaultField = 'at_' . $condition->getAttribute() . '_default.value';
            $defaultValue = $this->_connection->quoteIdentifier($defaultField);
        }

        $ifNullConditionField = (string) $this->_connection->getIfNullSql(
            $this->_connection->quoteIdentifier($argument),
            $defaultValue
        );

        //operator 'contains {}' is mapped to 'IN()' query that cannot work with substrings
        // adding mapping to 'LIKE %%'
        if ($condition->getInputType() === 'string'
            && array_key_exists($conditionOperator, $this->stringConditionOperatorMap)
        ) {
            $sql = str_replace(
                ':field',
                $ifNullConditionField,
                $this->stringConditionOperatorMap[$conditionOperator]
            );
            $bindValue = $condition->getBindArgumentValue();
            $expression = $value . $this->_connection->quoteInto($sql, "%$bindValue%");
        } else {
            $sql = str_replace(
                ':field',
                $ifNullConditionField,
                $this->_conditionOperatorMap[$conditionOperator]
            );
            $bindValue = $condition->getBindArgumentValue();
            $expression = $value . $this->_connection->quoteInto($sql, $bindValue);
        }
        // values for multiselect attributes can be saved in comma-separated format
        // below is a solution for matching such conditions with selected values
        if (is_array($bindValue) && \in_array($conditionOperator, ['()', '{}'], true)) {
            foreach ($bindValue as $item) {
                $expression .= $this->_connection->quoteInto(
                    " OR (FIND_IN_SET (?, {$ifNullConditionField}) > 0)",
                    $item
                );
            }
        }

        return (string) $this->_expressionFactory->create(
            ['expression' => $expression]
        );
    }

    /**
     * Check is default store used.
     *
     * @param AbstractCollection $collection
     *
     * @return bool
     */
    private function checkIsDefaultStoreUsed(AbstractCollection $collection): bool
    {
        return (int) $collection->getStoreId() === (int) $collection->getDefaultStoreId();
    }

    /**
     * Check if attribute can have default value.
     *
     * @param string $attributeCode
     * @param bool $isDefaultStoreUsed
     *
     * @return bool
     */
    private function canAttributeHaveDefaultValue(string $attributeCode, bool $isDefaultStoreUsed): bool
    {
        if ($isDefaultStoreUsed) {
            return false;
        }

        try {
            $attribute = $this->attributeRepository->get(Product::ENTITY, $attributeCode);
        } catch (NoSuchEntityException $e) {
            // It's not exceptional case as we want to check if we have such attribute or not
            return false;
        }

        return !$attribute->isScopeGlobal();
    }
}
