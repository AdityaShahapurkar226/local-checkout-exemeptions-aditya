<?php
/**
 * Created by Q-Solutions Studio
 * Date: 30.09.2019
 *
 * @category    DataFeedWatch
 * @package     DataFeedWatch_Connector
 * @author      Wojciech M. Wnuk <wojtek@qsolutionsstudio.com>
 */

namespace DataFeedWatch\Connector\Plugin;

use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Module\Manager;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\Store\Model\StoreManagerInterface;

class Quantity extends ExtensionAttributeAbstract
{
    protected const STOCK_TABLE = "inventory_source_item";
    protected const LEGACY_STOCK_TABLE = "cataloginventory_stock_item";
    /**
     * @var Manager
     */
    protected $moduleManager;
    /**
     * @var StoreManagerInterface
     */
     protected StoreManagerInterface $storeManager;

    /**
     * ParentIds constructor.
     * @param ProductExtensionFactory $extensionFactory
     * @param ResourceConnection $resourceConnection
     * @param Manager $moduleManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductExtensionFactory $extensionFactory,
        ResourceConnection $resourceConnection,
        Manager $moduleManager,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($extensionFactory, $resourceConnection);
        $this->moduleManager = $moduleManager;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    protected function getExtensionData(Product $product)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName(self::STOCK_TABLE);

        if ($this->moduleManager->isEnabled('Magento_Inventory')) {
            $quantity = $this->getSalableQty($product);
        } else {
            $tableName = $this->resourceConnection->getTableName(self::LEGACY_STOCK_TABLE);

            $query = sprintf(
                "SELECT SUM(`qty`) FROM `%s` WHERE `product_id` = :product_id AND `is_in_stock` = 1%s",
                $tableName,
                $product->getWebsiteId() ? " AND `website_id` = :website_id" : ''
            );
            $bind = ['product_id' => $product->getId()];
            if ($product->getWebsiteId()) {
                $bind['website_id'] = $product->getWebsiteId();
            }
            $quantity = $connection->fetchOne($query, $bind);
        }

        return $quantity ?: 0;
    }

    /**
     * @inheritdoc
     */
    protected function getDataVar(): string
    {
        return 'Quantity';
    }

    /**
     * Get Salable quantity for a product
     *
     * @param Product $product
     * @return float
     */
    protected function getSalableQty(Product $product): float
    {
        if (!$product->getTypeInstance()->isComposite($product)) {
            /** @var \Magento\InventorySalesApi\Api\StockResolverInterface */
            $stockResolver = ObjectManager::getInstance()->get(
                \Magento\InventorySalesApi\Api\StockResolverInterface::class
            );
            /** @var \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface */
            $getProductSalableQty = ObjectManager::getInstance()->get(
                \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface::class
            );
            /** @var \Magento\InventoryApi\Api\SourceRepositoryInterface */
            $websiteCode = $this->storeManager->getWebsite($product->getWebsiteId())->getCode();
            $stockId = $stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();

            return $getProductSalableQty->execute($product->getSku(), $stockId);
        }

        return 0.0;
    }
}
