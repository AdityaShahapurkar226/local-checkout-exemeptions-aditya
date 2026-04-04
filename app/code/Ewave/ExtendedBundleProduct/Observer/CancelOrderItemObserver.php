<?php
namespace Ewave\ExtendedBundleProduct\Observer;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Framework\Module\Manager as ModuleManager;

/**
 * Catalog inventory module observer
 */
class CancelOrderItemObserver implements ObserverInterface
{
    /**
     * @var StockManagementInterface
     */
    protected $stockManagement;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $priceIndexer;

    /**
     * @var ProductResource
     */
    protected $productResource;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @param StockManagementInterface $stockManagement
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
     * @param ProductResource $productResource
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        StockManagementInterface $stockManagement,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer,
        ProductResource $productResource,
        ModuleManager $moduleManager = null
    ) {
        $this->stockManagement = $stockManagement;
        $this->priceIndexer = $priceIndexer;
        $this->productResource = $productResource;
        $this->moduleManager = $moduleManager ?: ObjectManager::getInstance()->get(ModuleManager::class);
    }

    /**
     * Cancel order item
     *
     * @param   EventObserver $observer
     * @return  void
     */
    public function execute(EventObserver $observer)
    {
        if ($this->moduleManager->isEnabled('Magento_InventorySalesApi')) {
            return;
        }

        /** @var \Magento\Sales\Model\Order\Item $item */
        $item = $observer->getEvent()->getItem();
        $children = $item->getChildrenItems();
        $parentItem = $item->getParentItem();
        $qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();
        if ($parentItem && $parentItem->getProductType() == Bundle::TYPE_CODE
            && $item->getProductType() == Configurable::TYPE_CODE
            && $item->getId() && $item->getProductId() && empty($children) && $qty
        ) {
            $simpleSku = $item->getProductOptionByCode('simple_sku');
            if ($simpleSku && ($simpleId = $this->productResource->getIdBySku($simpleSku))) {
                $this->stockManagement->backItemQty($simpleId, $qty, $item->getStore()->getWebsiteId());
                $this->priceIndexer->reindexRow($simpleId);
            }
        }
    }
}
