<?php

namespace Ewave\ExtendedBundleProduct\Plugin\Magento\Bundle\Model\ResourceModel\Indexer;

use Magento\Bundle\Model\ResourceModel\Indexer\Price as Subject;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPrice;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\DefaultPriceFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class PricePlugin
 */
class PricePlugin
{
    /**
     * @var DefaultPriceFactory
     */
    protected $defaultPriceFactory;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * PricePlugin constructor.
     *
     * @param DefaultPriceFactory $defaultPriceFactory
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        DefaultPriceFactory $defaultPriceFactory,
        MetadataPool $metadataPool
    ) {
        $this->metadataPool = $metadataPool;
        $this->defaultPriceFactory = $defaultPriceFactory;
    }

    /**
     * @param Subject $subject
     * @param Subject $result
     * @return Subject
     */
    public function afterReindexAll(
        Subject $subject,
        $result
    ) {
        $this->reindex($subject);
        return $result;
    }

    /**
     * @param Subject $subject
     * @param null $ids
     * @param Subject $result
     * @return Subject
     */
    public function afterReindexEntity(
        Subject $subject,
        $ids,
        $result
    ) {
        $this->reindex($subject, $ids);
        return $result;
    }

    /**
     * @param Subject $subject
     * @param array $ids
     * @return void
     */
    protected function reindex(Subject $subject, $ids = [])
    {
        $conn = $subject->getConnection();

        $linkedField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();

        $select = $conn->select()->from(
            ['main' => $conn->getTableName('catalog_product_bundle_selection')],
            ['product.entity_id']
        )
            ->join(
                ['product' => $conn->getTableName('catalog_product_entity')],
                'main.parent_product_id = product.' . $linkedField,
                []
            )
            ->join(
                ['config_product' => $conn->getTableName('catalog_product_entity')],
                'main.product_id = config_product.entity_id',
                []
            )
            ->where('config_product.type_id = ?', Configurable::TYPE_CODE)
            ->group('product.entity_id');

        $entityIds = $conn->fetchCol($select);

        /** @var DefaultPrice $priceIndexer */
        $priceIndexer = $this->defaultPriceFactory->create();
        $priceIndexer->setTypeId(Bundle::TYPE_CODE);
        $priceIndexer->setIsComposite(true);
        try {
            $priceIndexer->reindexEntity($entityIds);
        } catch (\Exception $e) {
            //something went wrong...
        }
    }
}
