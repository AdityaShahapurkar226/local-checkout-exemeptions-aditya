<?php
namespace Ewave\ExtendedBundleProduct\Preferences\Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderInterface;
use Magento\ConfigurableProduct\Pricing\Price\LowestPriceOptionsProviderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;

/**
 * Class LowestPriceOptionsProvider
 * @package Ewave\ExtendedBundleProduct\Preferences\Magento\ConfigurableProduct\Pricing\Price
 */
class LowestPriceOptionsProvider implements LowestPriceOptionsProviderInterface
{
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var LinkedProductSelectBuilderInterface
     */
    protected $linkedProductSelectBuilder;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Key is product id and store id. Value is array of prepared linked products
     *
     * @var array
     */
    protected $linkedProductMap;

    /**
     * LowestPriceOptionsProvider constructor.
     * @param ResourceConnection $resourceConnection
     * @param LinkedProductSelectBuilderInterface $linkedProductSelectBuilder
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface|null $storeManager
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        LinkedProductSelectBuilderInterface $linkedProductSelectBuilder,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager = null
    ) {
        $this->resource = $resourceConnection;
        $this->linkedProductSelectBuilder = $linkedProductSelectBuilder;
        $this->collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
    }

    /**
     * Implementation of the getting for not from all configurable children
     * but only preselected (if they are).
     *
     * @param ProductInterface $product
     * @return ProductInterface[]|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProducts(ProductInterface $product)
    {
        $key = $this->storeManager->getStore()->getId() . '-' . $product->getId();
        if (!isset($this->linkedProductMap[$key])) {
            $configurableOptions = json_decode((string)$product->getConfigurableOptions());
            if (!empty($configurableOptions)) {
                $productIds = $configurableOptions;
            } else {
                $productIds = $this->resource->getConnection()->fetchCol(
                    '(' . implode(') UNION (', $this->linkedProductSelectBuilder->build($product->getId(), $this->storeManager->getStore()->getId())) . ')'
                );
            }
            $this->linkedProductMap[$key] = $this->collectionFactory->create()
                ->addAttributeToSelect(
                    ['price', 'special_price', 'special_from_date', 'special_to_date', 'tax_class_id']
                )
                ->addIdFilter($productIds)
                ->getItems();
        }

        return $this->linkedProductMap[$key];
    }
}
