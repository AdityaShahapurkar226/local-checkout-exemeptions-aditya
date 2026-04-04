<?php
namespace Webkul\MarketplaceBaseShipping\Plugin\Marketplace\Controller\Order;

class View
{
    public function __construct(
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\Marketplace\Helper\Orders $orderHelper,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader
    ) {
        $this->mpHelper = $mpHelper;
        $this->shipmentLoader = $shipmentLoader;
        $this->orderHelper = $orderHelper;
    }

    public function beforeExecute(\Webkul\Marketplace\Controller\Order\View $subject)
    {
        $id = $subject->getRequest()->getParam('id');
        $order = \Magento\Framework\App\ObjectManager::getInstance()->create(
            \Magento\Sales\Model\Order::class
        )->load($id);
        if ($id && $order->canShip()) {
            $tracking = $this->orderHelper->getOrderinfo($id);
            if (!empty($tracking)) {
                $this->shipmentLoader->setShipmentId($subject->getRequest()->getParam('shipment_id'));
                $this->shipmentLoader->setOrderId($subject->getRequest()->getParam('id'));
                $this->shipmentLoader->setShipmentId($tracking->getShipmentId());
                
                if (!$order->canShip() && !$tracking->getShipmentId()) {
                    $shipment = $order->getShipmentsCollection()->getFirstItem();
                    $shipmentId = $shipment->getId();
                    $this->shipmentLoader->setShipmentId($shipmentId);
                }
                $shipment = $this->shipmentLoader->load();
            }
        }
    }
}
