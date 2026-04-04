<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Rule\Condition;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Phrase;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;

class Bestsellers extends AbstractCondition
{
    private const FLAG = 'sorting_bestseller';
    private const BESTSELLER_TABLE = 'amasty_sorting_bestsellers';

    /**
     * @var FormatInterface
     */
    private $format;

    /**
     * @var ResourceConnection
     */
    private $resource;

    public function __construct(
        Context $context,
        FormatInterface $format,
        ResourceConnection $resource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->format = $format;
        $this->resource = $resource;
    }

    /**
     * @param ProductCollection $collection
     * @return void
     */
    public function collectValidatedAttributes(ProductCollection $collection): void
    {
        if (!$collection->getFlag(self::FLAG)) {
            $collection->setFlag(self::FLAG);
            $bestSellerTable = $this->resource->getTableName(self::BESTSELLER_TABLE);

            if ($this->resource->getConnection()->isTableExists($bestSellerTable)) {
                $collection->getSelect()->joinLeft(
                    ['bestseller' => $bestSellerTable],
                    sprintf(
                        'e.entity_id = bestseller.product_id AND bestseller.store_id = %d',
                        $collection->getStoreId()
                    ),
                    ['qty_ordered']
                );
            }
        }
    }

    public function validate(AbstractModel $model): bool
    {
        $attribute = $this->getAttribute();

        if ($model->hasData($attribute) && $model->getData($attribute) === null) {
            return false;
        }

        return parent::validate($model);
    }

    /**
     * @return string
     */
    public function getAttribute(): string
    {
        return 'qty_ordered';
    }

    /**
     * @return Phrase
     */
    public function getAttributeElementHtml()
    {
        return __('Bestseller Sales');
    }

    public function getInputType(): string
    {
        return 'numeric';
    }

    public function getValueElementType(): string
    {
        return 'text';
    }

    public function getDefaultOperatorOptions(): array
    {
        $values = parent::getDefaultOperatorOptions();
        unset(
            $values['{}'],
            $values['!{}'],
            $values['<=>']
        );

        return $values;
    }

    public function loadArray($arr): AbstractCondition
    {
        $tmp = [];

        foreach (explode(',', ($arr['value'] ?? '')) as $value) {
            $tmp[] = $this->format->getNumber($value);
        }

        $arr['value'] = implode(',', $tmp);

        return parent::loadArray($arr);
    }

    public function getMappedSqlField(): string
    {
        return 'bestseller.qty_ordered';
    }
}
