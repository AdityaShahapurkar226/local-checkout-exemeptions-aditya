<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Indexer\TogetherCondition;

use Generator;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Query\Generator as BatchQueryGenerator;

class GetProductIds
{
    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var BatchQueryGenerator
     */
    private $batchQueryGenerator;

    /**
     * @var Visibility
     */
    private $visibility;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var int
     */
    private $batchSize;

    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        BatchQueryGenerator $batchQueryGenerator,
        Visibility $visibility,
        ResourceConnection $resourceConnection,
        int $batchSize = 1000
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->batchQueryGenerator = $batchQueryGenerator;
        $this->visibility = $visibility;
        $this->resourceConnection = $resourceConnection;
        $this->batchSize = $batchSize;
    }

    public function execute(int $storeId): Generator
    {
        /** @var ProductCollection $productCollection */
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addStoreFilter($storeId);
        $productCollection->setVisibility($this->visibility->getVisibleInCatalogIds());
        $batchSelectIterator = $this->batchQueryGenerator->generate(
            'entity_id',
            $productCollection->getSelect(),
            $this->batchSize
        );

        foreach ($batchSelectIterator as $batchQuery) {
            foreach ($this->resourceConnection->getConnection()->fetchCol($batchQuery) as $productId) {
                yield (int) $productId;
            }
        }
    }
}
