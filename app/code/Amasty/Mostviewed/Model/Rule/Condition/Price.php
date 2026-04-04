<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Rule\Condition;

use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class Price extends \Magento\CatalogRule\Model\Rule\Condition\Product
{
    /**
     * @var array
     */
    private $inverseMap = [
        '==' => '!=',
        '!=' => '==',
        '<' => '>',
        '<=' => '>=',
        '>' => '<',
        '>=' => '<='
    ];

    public function apply(Collection $collection, ProductModel $product, bool $inverse = false): bool
    {
        $result = false;

        $price = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();

        $type = $this->getTypeByOperator($inverse);

        if ($type) {
            $collection->addFieldToFilter('price', [$type => $price]);
            $result = true;
        }

        return $result;
    }

    private function getTypeByOperator(bool $inverse): ?string
    {
        $type = null;

        $operator = $inverse ? $this->inverseMap[$this->getOperator()] : $this->getOperator();
        switch ($operator) {
            case '==':
                $type = 'eq';
                break;
            case '!=':
                $type = 'neq';
                break;
            case '<':
                $type = 'lt';
                break;
            case '<=':
                $type = 'lteq';
                break;
            case '>':
                $type = 'gt';
                break;
            case '>=':
                $type = 'gteq';
                break;
        }

        return $type;
    }

    /**
     * @return array
     */
    public function getDefaultOperatorInputByType()
    {
        $this->_defaultOperatorInputByType = parent::getDefaultOperatorInputByType();
        $this->_arrayInputTypes[] = 'price';
        $this->_defaultOperatorInputByType['price'] = ['==', '!=', '>=', '>', '<=', '<'];

        return $this->_defaultOperatorInputByType;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return 'price';
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getAttributeElementHtml()
    {
        return __('Price');
    }

    /**
     * @return string
     */
    public function getValueElementHtml()
    {
        return __(' Current Product Price');
    }

    /**
     * @return string
     */
    protected function _getAttributeCode()
    {
        return 'price';
    }

    /**
     * @return array
     */
    public function getAttributeSelectOptions()
    {
        $opt = [
            [
                'value' => '1',
                'label' => __(' Current Product Price')
            ]
        ];

        return $opt;
    }

    /**
     * @return array
     */
    public function getValueSelectOptions()
    {
        return $this->getAttributeSelectOptions();
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        return 'select';
    }

    /**
     * @return string
     */
    public function getFormName()
    {
        return \Amasty\Mostviewed\Model\Group::FORM_NAME;
    }
}
