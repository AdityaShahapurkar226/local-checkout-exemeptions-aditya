<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Controller\Order;

/**
 * Webkul Marketplace Order Invoice Controller.
 */
class Invoice extends \Webkul\Marketplace\Controller\Order
{
    /**
     * Marketplace order invoice controller.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->helper;
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            if ($order = $this->_initOrder()) {
                $this->doInvoiceExecution($order);
                $this->doAdminShippingInvoiceExecution($order);

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/view',
                    [
                        'id' => $order->getEntityId(),
                        '_secure' => $this->getRequest()->isSecure(),
                    ]
                );
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/history',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Prepare shipment
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return \Magento\Sales\Model\Order\Shipment|false
     */
    protected function _prepareShipment($invoice)
    {
        $data = $this->getRequest()->getParam('invoice');
        $itemArr = [];
        if (!isset($data['items']) || empty($data['items'])) {
            $orderItems = $invoice->getOrder()->getItems();
            foreach ($orderItems as $item) {
                $itemArr[$item->getId()] = (int)$item->getQtyOrdered();
            }
        }
        $shipment = $this->_shipmentFactory->create(
            $invoice->getOrder(),
            isset($data['items']) ? $data['items'] : $itemArr,
            $this->getRequest()->getPost('tracking')
        );
        if (!$shipment->getTotalQty()) {
            return false;
        }

        return $shipment->register();
    }

    /**
     * Create invoice
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function doInvoiceExecution($order)
    {
        try {
            $helper = $this->helper;
            $sellerId = $helper->getCustomerId();
            $orderId = $order->getId();
            if ($order->canUnhold()) {
                $this->messageManager->addError(
                    __('Can not create invoice as order is in HOLD state')
                );
            } else {

                if (!$orderId) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The order no longer exists.')
                    );
                }
                
                if (!$order->canInvoice()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('The order can\'t be invoice.')
                    );
                }

                $data = $this->getRequest()->getParam('invoice', []);
                $invoiceItems = isset($data['items']) ? $data['items'] : [];

                $paymentCode = '';
                if ($order->getPayment()) {
                    $paymentCode = $order->getPayment()->getMethod();
                }

                $itemsData = $this->getCurrentSellerInvoiceItemsData(
                    $orderId,
                    $sellerId,
                    $paymentCode,
                    $invoiceItems
                );

                $items = $itemsData['items'];
                $currencyRate = $itemsData['currencyRate'];
                $codCharges = $itemsData['codCharges'];
                $tax = $itemsData['tax'];
                $couponAmount = $itemsData['couponAmount'];
                $shippingAmount = $itemsData['shippingAmount'];

                $invoice = $this->invoiceService->prepareInvoice($order, $items);
                if (!$invoice) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('We can\'t save the invoice right now.')
                    );
                }
                if (!$invoice->getTotalQty()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('You can\'t create an invoice without products.')
                    );
                }
                $this->_coreRegistry->register(
                    'current_invoice',
                    $invoice
                );
                if (!empty($data['capture_case'])) {
                    $invoice->setRequestedCaptureCase(
                        $data['capture_case']
                    );
                }

                if (!empty($data['comment_text'])) {
                    $invoice->addComment(
                        $data['comment_text'],
                        isset($data['comment_customer_notify']),
                        isset($data['is_visible_on_front'])
                    );

                    $invoice->setCustomerNote($data['comment_text']);
                    $invoice->setCustomerNoteNotify(
                        isset($data['comment_customer_notify'])
                    );
                }

                $currentCouponAmount = $currencyRate * $couponAmount;
                $currentShippingAmount = $currencyRate * $shippingAmount;
                $currentTaxAmount = $currencyRate * $tax;
                $currentCodcharges = $currencyRate * $codCharges;
                $taxAmountInv = $order->getBaseTaxAmount() - $order->getBaseShippingTaxAmount();
                $currentTaxAmountInv = $order->getTaxAmount() - $order->getShippingTaxAmount();
                $invoice->setBaseShippingTaxAmount(0);
                $invoice->setShippingTaxAmount(0);
                $invoice->setTaxAmount($currentTaxAmountInv);
                $invoice->setBaseTaxAmount($taxAmountInv);
                $invoice->setBaseDiscountAmount($couponAmount);
                $invoice->setDiscountAmount($currentCouponAmount);
                $invoice->setShippingAmount($currentShippingAmount);
                $invoice->setShippingInclTax($shippingAmount);
                $invoice->setBaseShippingInclTax($shippingAmount);
                $invoice->setBaseShippingAmount($shippingAmount);
                if ($paymentCode == 'mpcashondelivery') {
                    $invoice->setMpcashondelivery($currentCodcharges);
                    $invoice->setBaseMpcashondelivery($codCharges);
                }
                $invoice->setGrandTotal(
                    $invoice->getSubtotal() +
                    $currentShippingAmount +
                    $currentCodcharges +
                    $currentTaxAmount -
                    $currentCouponAmount
                );
                $invoice->setBaseGrandTotal(
                    $invoice->getBaseSubtotal() +
                    $shippingAmount +
                    $codCharges +
                    $tax -
                    $couponAmount
                );

                $invoice->register();

                $invoice->getOrder()->setCustomerNoteNotify(
                    !empty($data['send_email'])
                );
                $invoice->getOrder()->setIsInProcess(true);

                $transactionSave = $this->_objectManager->create(
                    \Magento\Framework\DB\Transaction::class
                )->addObject(
                    $invoice
                )->addObject(
                    $invoice->getOrder()
                );
                $shipment = false;
                $shipmentId = '';
                if (!empty($data['do_shipment']) || (int)$invoice->getOrder()->getForcedShipmentWithInvoice()) {
                    $shipment = $this->_prepareShipment($invoice);
                    if ($shipment) {
                        $transactionSave->addObject($shipment);
                        $shipmentId = $shipment->getId();
                    }
                }
                $transactionSave->save();

                $invoiceId = $invoice->getId();

                if ($shipment) {
                    $shipmentId = $shipment->getId();
                }

                $this->_invoiceSender->send($invoice);

                $this->messageManager->addSuccess(
                    __('Invoice has been created for this order.')
                );

                /*update mpcod table records*/
                if ($invoiceId != '') {
                    if ($paymentCode == 'mpcashondelivery') {
                        $saleslistColl = $this->saleslistFactory->create()
                        ->getCollection()
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
                            ['in' => $items]
                        );
                        foreach ($saleslistColl as $saleslist) {
                            $saleslist->setCollectCodStatus(1);
                            $saleslist->save();
                        }
                    }

                    $trackingcol1 = $this->mpOrdersModel->create()
                    ->getCollection()
                    ->addFieldToFilter(
                        'order_id',
                        $orderId
                    )
                    ->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    );
                    foreach ($trackingcol1 as $row) {
                        $invoiceIds = explode(',', $row->getInvoiceId());
                        array_push($invoiceIds, $invoiceId);
                        $row->setInvoiceId(implode(',', $invoiceIds));
                        if ($shipmentId) {
                            $shipmentIds = explode(',', $row->getShipmentId());
                            array_push($shipmentIds, $shipmentId);
                            $row->setShipmentId(implode(',', $shipmentIds));
                        }
                        if ($row->getShipmentId()) {
                            $row->setOrderStatus('complete');
                        } else {
                            $row->setOrderStatus('processing');
                        }
                        $row->save();
                    }
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Order_Invoice doInvoiceExecution : ".$e->getMessage()
            );
            $this->messageManager->addError(
                __('We can\'t save the invoice right now.')
            );
            $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * Create adin shipping invoice
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function doAdminShippingInvoiceExecution($order)
    {
        try {
            $paymentCode = '';
            $paymentMethod = '';
            if ($order->getPayment()) {
                $paymentCode = $order->getPayment()->getMethod();
            }
            if (!$order->canUnhold() && ($order->getGrandTotal() > $order->getTotalPaid())) {
                $isAllItemInvoiced = $this->isAllItemInvoiced($order);

                if ($isAllItemInvoiced && $order->getShippingAmount()) {
                    $invoice = $this->_objectManager->create(
                        \Magento\Sales\Model\Service\InvoiceService::class
                    )->prepareInvoice(
                        $order,
                        []
                    );
                    if (!$invoice) {
                        throw new \Magento\Framework\Exception\LocalizedException(
                            __('We can\'t save the invoice right now.')
                        );
                    }

                    $baseSubtotal = $order->getBaseShippingAmount();
                    $subtotal = $order->getShippingAmount();

                    if (!empty($data['capture_case'])) {
                        $invoice->setRequestedCaptureCase(
                            $data['capture_case']
                        );
                    }

                    if (!empty($data['comment_text'])) {
                        $invoice->setCustomerNote($data['comment_text']);
                        $invoice->addComment(
                            $data['comment_text'],
                            isset($data['comment_customer_notify']),
                            isset($data['is_visible_on_front'])
                        );

                        $invoice->setCustomerNoteNotify(
                            isset($data['comment_customer_notify'])
                        );
                    }
                    $invoice->setShippingAmount($subtotal);
                    $invoice->setBaseShippingAmount($baseSubtotal);
                    $invoice->setBaseShippingInclTax($baseSubtotal + $order->getBaseShippingTaxAmount());
                    $invoice->setShippingInclTax($baseSubtotal + $order->getShippingTaxAmount());
                    $invoice->setSubtotal(0);
                    $invoice->setBaseSubtotal(0);
                    $baseSubtotal = $order->getBaseShippingAmount() + $order->getBaseShippingTaxAmount();
                    $subtotal = $order->getShippingAmount() + $order->getShippingTaxAmount();
                    $invoice->setGrandTotal($subtotal);
                    $invoice->setBaseGrandTotal($baseSubtotal);
                    $invoice->setTaxAmount($order->getShippingTaxAmount());
                    $invoice->setBaseTaxAmount($order->getBaseShippingTaxAmount());
                    $invoice->register();

                    $invoice->getOrder()->setCustomerNoteNotify(
                        !empty($data['send_email'])
                    );
                    $invoice->getOrder()->setIsInProcess(true);

                    $transactionSave = $this->_objectManager->create(
                        \Magento\Framework\DB\Transaction::class
                    )->addObject(
                        $invoice
                    )->addObject(
                        $invoice->getOrder()
                    );
                    $transactionSave->save();

                    $this->_eventManager->dispatch(
                        'mp_order_shipping_invoice_save_after',
                        ['invoice' => $invoice, 'order' => $order]
                    );

                    $this->_invoiceSender->send($invoice);
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->helper->logDataInLogger(
                "Controller_Order_Invoice doAdminShippingInvoiceExecution : ".$e->getMessage()
            );
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Order_Invoice doAdminShippingInvoiceExecution : ".$e->getMessage()
            );
        }
    }
}
