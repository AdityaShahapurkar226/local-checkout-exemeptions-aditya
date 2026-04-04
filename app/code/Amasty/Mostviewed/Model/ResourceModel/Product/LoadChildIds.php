<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

class LoadChildIds
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    public function __construct(ResourceConnection $resourceConnection, MetadataPool $metadataPool)
    {
        $this->resourceConnection = $resourceConnection;
        $this->metadataPool = $metadataPool;
    }

    public function execute(int $parentProductId): array
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()->from(
            ['e' => $this->resourceConnection->getTableName('catalog_product_entity')],
            []
        )->join(
            ['cpr' => $this->resourceConnection->getTableName('catalog_product_relation')],
            sprintf(
                'e.%s = cpr.parent_id',
                $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField()
            ),
            ['child_id']
        )->where(
            'e.entity_id = ?',
            $parentProductId
        );

        return $connection->fetchCol($select);
    }
}
