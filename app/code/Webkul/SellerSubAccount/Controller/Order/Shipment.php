<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\SellerSubAccount\Controller\Order;

class Shipment extends \Webkul\Marketplace\Controller\Order\Shipment
{
    /**
     * Do Shipment Execution
     *
     * @param Order $order
     * @return void
     */
    public function doShipmentExecution($order)
    {
        try {
            $sellerId = $this->_customerSession->getCustomerId();
            $subAccount = $helper = $this->_objectManager->create(
                \Webkul\SellerSubAccount\Helper\Data::class
            )->getCurrentSubAccount();
            if ($subAccount->getId()) {
                $sellerId = $helper = $this->_objectManager->create(
                    \Webkul\SellerSubAccount\Helper\Data::class
                )->getSubAccountSellerId();
            }
            $orderId = $order->getId();
            $marketplaceOrder = $this->_objectManager->create(
                \Webkul\Marketplace\Helper\Orders::class
            )
            ->getOrderinfo($orderId);
            $trackingid = '';
            $carrier = '';
            $trackingData = [];
            $paramData = $this->getRequest()->getParams();
            if (!empty($paramData['tracking_id'])) {
                $trackingid = $paramData['tracking_id'];
                $trackingData[1]['number'] = $trackingid;
                $trackingData[1]['carrier_code'] = 'custom';
            }
            if (!empty($paramData['carrier'])) {
                $carrier = $paramData['carrier'];
                $trackingData[1]['title'] = $carrier;
            }
            if (!empty($paramData['api_shipment'])) {
                $this->_eventManager->dispatch(
                    'generate_api_shipment',
                    [
                    'api_shipment' => $paramData['api_shipment'],
                    'order_id' => $orderId,
                    ]
                );
                $shipmentData = $this->_customerSession->getData('shipment_data');
                $apiName = $shipmentData['api_name'];
                $trackingid = $shipmentData['tracking_number'];
                $trackingData[1]['number'] = $trackingid;
                $trackingData[1]['carrier_code'] = 'custom';
                $this->_customerSession->unsetData('shipment_data');
            }
            if (empty($paramData['api_shipment']) || $trackingid != '') {
                    $this->checkShipment($order, $sellerId, $trackingData, $trackingid, $carrier);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('We can\'t save the shipment right now.')
            );
            $this->messageManager->addError($e->getMessage());
        }
    }
    /**
     * Check Shipment
     *
     * @param collection $order
     * @param int $sellerId
     * @param collection $trackingData
     * @param int $trackingid
     * @param collection $carrier
     * @return void
     */
    private function checkShipment(
        $order = null,
        $sellerId = null,
        $trackingData = null,
        $trackingid = null,
        $carrier = null
    ) {
        $paramItems = [];
        $data = $this->getRequest()->getParam('shipment', []);
        $shipmentItems = isset($data['items']) ? $data['items'] : [];
        foreach ($shipmentItems as $shipmentItemId => $shipmentItemQty) {
            if ($shipmentItemQty) {
                array_push($paramItems, $shipmentItemId);
            }
        }
        $orderId = $order->getId();
        if ($order->canUnhold()) {
            $this->messageManager->addError(
                __('Can not create shipment as order is in HOLD state')
            );
        } else {
            $items = [];
            $shippingAmount = 0;
            $trackingsdata = $this->_objectManager->create(
                \Webkul\Marketplace\Model\Orders::class
            )->getCollection()
            ->addFieldToFilter(
                'order_id',
                $orderId
            )
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            );
            foreach ($trackingsdata as $tracking) {
                $shippingAmount = $tracking->getShippingCharges();
            }

            $collection = $this->_objectManager->create(
                \Webkul\Marketplace\Model\Saleslist::class
            )->getCollection()
            ->addFieldToFilter(
                'order_id',
                $orderId
            )
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            )
            ->addFieldToFilter(
                'order_item_id',
                ['in' => $paramItems]
            );
            foreach ($collection as $saleproduct) {
                // 8
                $orderItemId = $saleproduct['order_item_id'];
                if (isset($shipmentItems[$orderItemId])) {
                    $items[$saleproduct['order_item_id']] = $shipmentItems[$orderItemId];
                }
            }
            $itemsarray = $this->_getShippingItemQtys($order, $items);
            if (count($itemsarray) > 0) {
                $shipment = false;
                $shipmentId = 0;
                if (!empty($paramData['shipment_id'])) {
                    $shipmentId = $paramData['shipment_id'];
                }
                if ($shipmentId) {
                    $shipment = $this->_objectManager->create(
                        \Magento\Sales\Model\Order\Shipment::class
                    )->load($shipmentId);
                } elseif ($orderId) {
                    if ($order->getForcedDoShipmentWithInvoice()) {
                        $this->messageManager
                        ->addError(
                            __('Cannot do shipment for the order separately from invoice.')
                        );
                    }
                    if (!$order->canShip()) {
                        $this->messageManager->addError(
                            __('Cannot do shipment for the order.')
                        );
                    }
                    $shipment = $this->_prepareShipment(
                        $order,
                        $itemsarray['data'],
                        $trackingData
                    );
                }
                    $comment = '';
                    $shipment->getOrder()->setCustomerNoteNotify(
                        !empty($data['send_email'])
                    );
                    $shippingLabel = '';
                if (!empty($data['create_shipping_label'])) {
                        $shippingLabel = $data['create_shipping_label'];
                }
                    $isNeedCreateLabel=!empty($shippingLabel) && $shippingLabel;
                    $shipment->getOrder()->setIsInProcess(true);
                    $transactionSave = $this->_objectManager->create(
                        \Magento\Framework\DB\Transaction::class
                    )->addObject(
                        $shipment
                    )->addObject(
                        $shipment->getOrder()
                    );
                    $transactionSave->save();
                    $shipmentId = $shipment->getId();
                    $courrier = 'custom';
                    $sellerCollection = $this->_objectManager->create(
                        \Webkul\Marketplace\Model\Orders::class
                    )->getCollection()
                    ->addFieldToFilter(
                        'order_id',
                        ['eq' => $orderId]
                    )
                    ->addFieldToFilter(
                        'seller_id',
                        ['eq' => $sellerId]
                    );
                foreach ($sellerCollection as $row) {
                    if ($shipment->getId() != '') {
                            $row->setShipmentId($shipment->getId());
                            $row->setTrackingNumber($trackingid);
                            $row->setCarrierName($carrier);
                        if ($row->getInvoiceId()) {
                                $row->setOrderStatus('complete');
                        } else {
                                $row->setOrderStatus('processing');
                        }
                            $row->save();
                    }
                }
                    $this->_shipmentSender->send($shipment);
                    $shipmentCreatedMessage = __('The shipment has been created.');
                    $labelMessage = __('The shipping label has been created.');
                    $this->messageManager->addSuccess(
                        $isNeedCreateLabel ? $shipmentCreatedMessage.' '.$labelMessage
                        : $shipmentCreatedMessage
                    );
            }
        }
    }
}
