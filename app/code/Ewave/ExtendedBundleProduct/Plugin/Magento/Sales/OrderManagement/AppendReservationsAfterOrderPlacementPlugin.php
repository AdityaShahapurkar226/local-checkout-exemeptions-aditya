<?php
namespace Ewave\ExtendedBundleProduct\Plugin\Magento\Sales\OrderManagement;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Bundle\Model\Product\Type as Bundle;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\App\ObjectManager;

class AppendReservationsAfterOrderPlacementPlugin
{
    /**
     * @var WebsiteRepositoryInterface
     */
    protected $websiteRepository;

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        WebsiteRepositoryInterface $websiteRepository,
        ModuleManager $moduleManager
    ) {
        $this->websiteRepository = $websiteRepository;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param OrderManagementInterface $subject
     * @param OrderInterface $order
     * @return OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPlace(OrderManagementInterface $subject, OrderInterface $order)
    {
        if (!$this->moduleManager->isEnabled('Magento_InventorySalesApi')) {
            return $order;
        }

        $itemsBySku = $itemsToSell = [];
        foreach ($order->getItems() as $item) {
            $parentItem = $item->getParentItem();
            if ($parentItem && $parentItem->getProductType() == Bundle::TYPE_CODE
                && $item->getProductType() == Configurable::TYPE_CODE
            ) {
                if (!isset($itemsBySku[$item->getSku()])) {
                    $itemsBySku[$item->getSku()] = 0;
                }
                $itemsBySku[$item->getSku()] += $item->getQtyOrdered();
            }
        }

        foreach ($itemsBySku as $sku => $qty) {
            $itemsToSell[] = $this->getItemsToSellFactory()->create([
                'sku' => $sku,
                'qty' => -(float)$qty
            ]);
        }

        $websiteId = (int)$order->getStore()->getWebsiteId();
        $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();

        $salesEvent = $this->getSalesEventFactory()->create([
            'type' => 'order_placed',
            'objectType' => 'order',
            'objectId' => (string)$order->getEntityId()
        ]);

        $salesChannel = $this->getSalesChannelFactory()->create([
            'data' => [
                'type' => 'website',
                'code' => $websiteCode
            ]
        ]);

        $this->getPlaceReservationsForSalesEvent()->execute($itemsToSell, $salesChannel, $salesEvent);
        return $order;
    }

    /**
     * @return object
     */
    private function getItemsToSellFactory()
    {
        return ObjectManager::getInstance()
            ->get('Magento\InventorySalesApi\Api\Data\ItemToSellInterfaceFactory');
    }

    /**
     * @return object
     */
    private function getSalesEventFactory()
    {
        return ObjectManager::getInstance()
            ->get('Magento\InventorySalesApi\Api\Data\SalesEventInterfaceFactory');
    }

    /**
     * @return object
     */
    private function getSalesChannelFactory()
    {
        return ObjectManager::getInstance()
            ->get('Magento\InventorySalesApi\Api\Data\SalesChannelInterfaceFactory');
    }

    /**
     * @return object
     */
    private function getPlaceReservationsForSalesEvent()
    {
        return ObjectManager::getInstance()
            ->get('Magento\InventorySalesApi\Api\PlaceReservationsForSalesEventInterface');
    }
}
