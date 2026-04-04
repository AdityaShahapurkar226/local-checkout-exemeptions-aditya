<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\OrdersFactory as MpOrdersModel;

class AdminhtmlSalesOrderShipmentAfter implements ObserverInterface
{
    /**
     * @var MpOrdersModel
     */
    protected $mpOrdersModel;
    /**
     * Construct
     *
     * @param MpOrdersModel $mpOrdersModel
     */
    public function __construct(
        MpOrdersModel $mpOrdersModel
    ) {
        $this->mpOrdersModel = $mpOrdersModel;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $shipmentId = $shipment->getId();
        /** @var \Magento\Sales\Model\Order $order */
        $order = $shipment->getOrder();
        $sellerCollection = $this->mpOrdersModel->create()
                ->getCollection()
                ->addFieldToFilter(
                    'order_id',
                    ['eq' => $order->getId()]
                )
                ->addFieldToFilter(
                    'seller_id',
                    ['neq' => 0]
                );

        foreach ($sellerCollection as $row) {
            if ($shipment->getId() != '') {
                $shipmentIds = explode(',', $row->getShipmentId());
                array_push($shipmentIds, $shipment->getId());
                $row->setShipmentId(implode(',', $shipmentIds));
                if ($row->getInvoiceId()) {
                    $row->setOrderStatus('complete');
                } else {
                    $row->setOrderStatus('processing');
                }
                $row->save();
            }
        }
    }
}
