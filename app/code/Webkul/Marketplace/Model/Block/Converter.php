<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Model\Block;

/**
 * Webkul Marketplace Converter Block
 */
class Converter
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
     */
    private $attributeCollectionFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory
     */
    private $attrOptionCollectionFactory;

    /**
     * @var array
     */
    private $attributeCodeOptionsPair;

    /**
     * @var array
     */
    private $attributeCodeOptionValueIdsPair;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $attributeCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->attrOptionCollectionFactory = $attrOptionCollectionFactory;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Convert CSV format row to array
     *
     * @param  array $row
     * @return array
     */
    public function convertRow($row)
    {
        $dataArray = [];
        foreach ($row as $field => $value) {
            if ('content' == $field) {
                $dataArray['block'][$field] = $this->replaceMatches($value);
                continue;
            }
            $dataArray['block'][$field] = $value;
        }
        return $dataArray;
    }

    /**
     * Function getCategoryByUrlKey
     *
     * @param string $urlKey
     * @return \Magento\Framework\Object
     */
    protected function getCategoryByUrlKey($urlKey)
    {
        $category = $this->categoryFactory->create()
            ->addAttributeToFilter('url_key', $urlKey)
            ->addUrlRewriteToResult();
        foreach ($category as $categoryModel) {
            return $categoryModel;
        }
        return [];
    }

    /**
     * Get formatted array value
     *
     * @param  mixed  $value
     * @param  string $separator
     * @return array
     */
    protected function getArrayValue($value, $separator = "/")
    {
        if (is_array($value)) {
            return $value;
        }
        if (false !== strpos($value, $separator)) {
            $value = array_filter(explode($separator, $value));
        }
        return !is_array($value) ? [$value] : $value;
    }

    /**
     * Function replaceMatches
     *
     * @param string $content
     * @return mixed
     */
    protected function replaceMatches($content)
    {
        $matchesArray = $this->getMatches($content);
        if (!empty($matchesArray['value'])) {
            $replaces = $this->getReplaces($matchesArray);
            $content = preg_replace($replaces['regexp'], $replaces['value'], $content);
        }
        return $content;
    }

    /**
     * Function getMatches
     *
     * @param string $content
     * @return array
     */
    protected function getMatches($content)
    {
        $regexp = '/{{(category[^ ]*) key="([^"]+)"}}/';
        preg_match_all($regexp, $content, $matchesCategory);
        $regexp = '/{{(product[^ ]*) sku="([^"]+)"}}/';
        preg_match_all($regexp, $content, $matchesProduct);
        $regexp = '/{{(attribute) key="([^"]*)"}}/';
        preg_match_all($regexp, $content, $matchesAttribute);
        $matchesArray = [
            'type' => $matchesCategory[1] + $matchesAttribute[1] + $matchesProduct[1],
            'value' => $matchesCategory[2] + $matchesAttribute[2] + $matchesProduct[2]
        ];
        return $matchesArray;
    }

    /**
     * Function getReplaces
     *
     * @param array $matches
     * @return array
     */
    protected function getReplaces($matches)
    {
        $replaceDataArray = [];

        foreach ($matches['value'] as $matchKey => $matchValue) {
            $typeValue = ucfirst(trim($matches['type'][$matchKey]));
            switch ($typeValue) {
                case "Category":
                    $matchResult = $this->matcherCategory($matchValue);
                    break;
                case "CategoryId":
                    $matchResult = $this->matcherCategoryId($matchValue);
                    break;
                case "Product":
                    $matchResult = $this->matcherProduct($matchValue);
                    break;
                case "Attribute":
                    $matchResult = $this->matcherAttribute($matchValue);
                    break;
            }
            if (!empty($matchResult)) {
                $replaceDataArray = array_merge_recursive($replaceDataArray, $matchResult);
            }
        }
        return $replaceDataArray;
    }

    /**
     * Get attribute options by attribute code
     *
     * @param  string $attributeCode
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection|null
     */
    protected function getAttributeOptions($attributeCode)
    {
        if (!$this->attributeCodeOptionsPair || !isset($this->attributeCodeOptionsPair[$attributeCode])) {
            $this->loadAttributeOptions($attributeCode);
        }
        $returnData = isset($this->attributeCodeOptionsPair[$attributeCode])
            ? $this->attributeCodeOptionsPair[$attributeCode]
            : null;
        return $returnData;
    }

    /**
     * Loads all attributes with options for attribute
     *
     * @param  string $attributeCode
     * @return $this
     */
    protected function loadAttributeOptions($attributeCode)
    {
        /**
         * Collection
         *
         * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $collection
        */
        $collection = $this->attributeCollectionFactory->create();
        $collection->addFieldToSelect(['attribute_code', 'attribute_id']);
        $collection->addFieldToFilter('attribute_code', $attributeCode);
        $collection->setFrontendInputTypeFilter(['in' => ['select', 'multiselect']]);
        foreach ($collection as $model) {
            $options = $this->getAllOptions($model->getAttributeId());
            $this->attributeCodeOptionsPair[$model->getAttributeCode()] = $options;
        }
        return $this;
    }

    /**
     * Function getAllOptions
     *
     * @param int $id
     * @return void
     */
    private function getAllOptions($id)
    {
        return $this->attrOptionCollectionFactory->create()
                ->setAttributeFilter($id)->setPositionOrder('asc', true)->load();
    }

    /**
     * Find attribute option value pair
     *
     * @param  string $attributeCode
     * @param  string $value
     * @return mixed
     */
    protected function getAttributeOptionValueId($attributeCode, $value)
    {
        if (!empty($this->attributeCodeOptionValueIdsPair[$attributeCode][$value])) {
            return $this->attributeCodeOptionValueIdsPair[$attributeCode][$value];
        }

        $options = $this->getAttributeOptions($attributeCode);
        $optArray = [];
        if ($options) {
            foreach ($options as $option) {
                $optArray[$option->getValue()] = $option->getId();
            }
        }
        $this->attributeCodeOptionValueIdsPair[$attributeCode] = $optArray;
        return $this->attributeCodeOptionValueIdsPair[$attributeCode][$value];
    }

    /**
     * Function matcherCategory
     *
     * @param string $matchValue
     * @return array
     */
    protected function matcherCategory($matchValue)
    {
        $replaceDataArray = [];
        $category = $this->getCategoryByUrlKey($matchValue);
        if (!empty($category)) {
            $categoryUrl = $category->getRequestPath();
            $replaceDataArray['regexp'][] = '/{{category key="' . $matchValue . '"}}/';
            $replaceDataArray['value'][] = '{{store url=""}}' . $categoryUrl;
        }
        return $replaceDataArray;
    }

    /**
     * Function matcherCategoryId
     *
     * @param string $matchValue
     * @return array
     */
    protected function matcherCategoryId($matchValue)
    {
        $replaceDataArray = [];
        $category = $this->getCategoryByUrlKey($matchValue);
        if (!empty($category)) {
            $replaceDataArray['regexp'][] = '/{{categoryId key="' . $matchValue . '"}}/';
            $replaceDataArray['value'][] = sprintf('%03d', $category->getId());
        }
        return $replaceDataArray;
    }

    /**
     * Function matcherProduct
     *
     * @param string $matchValue
     * @return array
     */
    protected function matcherProduct($matchValue)
    {
        $replaceDataArray = [];
        $productCollection = $this->productCollectionFactory->create();
        $productItem = $productCollection->addAttributeToFilter('sku', $matchValue)
            ->addUrlRewrite()
            ->getFirstItem();
        $productUrl = null;
        if ($productItem) {
            $productUrl = '{{store url=""}}' .  $productItem->getRequestPath();
        }
        $replaceDataArray['regexp'][] = '/{{product sku="' . $matchValue . '"}}/';
        $replaceDataArray['value'][] = $productUrl;
        return $replaceDataArray;
    }

    /**
     * Function matcherAttribute
     *
     * @param string $matchValue
     * @return array
     */
    protected function matcherAttribute($matchValue)
    {
        $replaceDataArray = [];
        if (strpos($matchValue, ':') === false) {
            return $replaceDataArray;
        }
        list($code, $value) = explode(':', $matchValue);

        if (!empty($code) && !empty($value)) {
            $replaceDataArray['regexp'][] = '/{{attribute key="' . $matchValue . '"}}/';
            $replaceDataArray['value'][] = sprintf('%03d', $this->getAttributeOptionValueId($code, $value));
        }
        return $replaceDataArray;
    }
}
