<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\CatalogRule\Model\Rule\Condition;

use Magento\CatalogRule\Model\Rule\Condition\Product;
use Magento\Catalog\Model\Product\Attribute\Repository as AttributeRepository;
use Magento\Framework\Exception\NoSuchEntityException;

class ProductPlugin
{
    /**
     * @var AttributeRepository
     */
    private $attributeRepository;

    /**
     * @var \Amasty\Mostviewed\Model\ResourceModel\Product
     */
    private $resource;

    public function __construct(
        AttributeRepository $attributeRepository,
        \Amasty\Mostviewed\Model\ResourceModel\Product $resource
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->resource = $resource;
    }

    public function aroundGetMappedSqlField(Product $subject, callable $proceed)
    {
        if ($subject->getAttribute()) {
            try {
                $this->attributeRepository->get($subject->getAttribute());
            } catch (NoSuchEntityException $e) {
                $connection = $this->resource->getConnection();
                $result = $connection->tableColumnExists(
                    $this->resource->getTable('catalog_product_entity'),
                    $subject->getAttribute()
                );
                if ($result) {
                    return 'e.' . $subject->getAttribute();
                }
            }
        }

        return $proceed();
    }
}
