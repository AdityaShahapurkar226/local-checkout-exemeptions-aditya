<?php

namespace Ewave\ExtendedBundleProduct\Plugin\Magento\CatalogInventory\Helper\Stock;

use Magento\CatalogInventory\Helper\Stock;
use Magento\Catalog\Model\Product;
use Magento\Bundle\Model\Product\Type;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Module\Manager as ModuleManager;

/**
 * Class AdaptAssignStatusToProductPlugin
 *
 * @package Ewave\ExtendedBundleProduct\Plugin\Magento\CatalogInventory\Helper\Stock
 */
class AdaptAssignStatusToProductPlugin
{
    public const TYPE_WEBSITE = 'website';

    /** @var StoreManagerInterface  */
    protected $storeManager;

    /** @var \Magento\InventorySalesApi\Api\StockResolverInterface  */
    protected $stockResolver;

    /** @var ProductRepositoryInterface  */
    protected $productRepository;
    
    /** @var ModuleManager  */
    protected $moduleManager;

    /**
     * AdaptAssignStatusToProductPlugin constructor.
     *
     * @param StoreManagerInterface         $storeManager
     * @param ModuleManager                 $moduleManager
     * @param ProductRepositoryInterface    $productRepository
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ModuleManager $moduleManager,
        ProductRepositoryInterface $productRepository
    ) {
        $this->storeManager = $storeManager;
        $this->moduleManager = $moduleManager;
        $this->productRepository = $productRepository;
    }

    /**
     * @param Stock $subject
     * @param null $result
     * @param Product $product
     * @param null $status
     * @return void
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterAssignStatusToProduct(
        Stock $subject,
        $result,
        Product $product,
        $status = null
    ) {
        if (!$this->moduleManager->isEnabled('Magento_InventorySalesApi')) {
            return $result;
        }
        if ($product->getTypeId() === Type::TYPE_CODE && !$status) {
            $website = $this->storeManager->getWebsite();
            $stock = $this->getStockResolver()->execute(self::TYPE_WEBSITE, $website->getCode());
            $options = $product->getTypeInstance()->getOptionsCollection($product);
            $hasSalable = false;
            $results = $this->getAreSalableSelections($product, $options->getItems(), $stock->getStockId());
            foreach ($results as $resultSelection) {
                if ($resultSelection->getIsSalable()) {
                    $hasSalable = true;
                    break;
                }
            }
            if ($hasSalable) {
                $product->setIsSalable(true);
            }
        }
    }

    /**
     * Get are bundle product selections salable.
     * @param ProductInterface $parentProduct
     * @param array $options
     * @param int $stockId
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getAreSalableSelections(
        ProductInterface $parentProduct,
        array $options,
        int $stockId
    ) {
        $bundleSelections = $parentProduct->getTypeInstance()
            ->getSelectionsCollection(array_keys($options), $parentProduct);
        $results = [];
        $storeId = $this->storeManager->getStore()->getId();
        foreach ($bundleSelections->getItems() as $selection) {
            if ((int)$selection->getStatus() === Status::STATUS_ENABLED
                && $selection->getTypeId() == Configurable::TYPE_CODE) {
                /** @var \Magento\Catalog\Model\Product $selectionProduct */
                $product = $this->productRepository->get($selection->getData('sku'), false, $storeId);
                $results[] = new \Magento\Framework\DataObject(
                    [
                        'sku' => $selection->getData('sku'),
                        'stock_id' => $stockId,
                        'is_salable' => $product->getIsSalable()
                    ]
                );
            }
        }

        return $results;
    }

    /**
     * @return \Magento\InventorySalesApi\Api\StockResolverInterface
     */
    protected function getStockResolver()
    {
        if ($this->stockResolver === null) {
            $this->stockResolver = ObjectManager::getInstance()->get(\Magento\InventorySalesApi\Api\StockResolverInterface::class);
        }

        return $this->stockResolver;
    }
}
