<?php
namespace Ewave\ExtendedBundleProduct\Observer;

use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\Manager as ModuleManager;

class RefundOrderInventoryObserver implements ObserverInterface
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
     * RefundOrderInventoryObserver constructor.
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
     * Return creditmemo items qty to stock
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if ($this->moduleManager->isEnabled('Magento_InventorySalesApi')) {
            return;
        }

        /* @var $creditMemo \Magento\Sales\Model\Order\Creditmemo */
        $creditMemo = $observer->getEvent()->getCreditmemo();
        foreach ($creditMemo->getItems() as $item) {
            /** @var \Magento\Sales\Model\Order\Creditmemo\Item $item */
            if ($item->getBackToStock() && ($orderItem = $item->getOrderItem())) {
                $simpleSku = $orderItem->getProductOptionByCode('simple_sku');
                if ($simpleSku && ($simpleId = $this->productResource->getIdBySku($simpleSku))) {
                    $this->stockManagement->backItemQty(
                        $simpleId,
                        $item->getQty(),
                        $orderItem->getStore()->getWebsiteId()
                    );
                    $this->priceIndexer->reindexRow($simpleId);
                }
            }
        }
    }
}
