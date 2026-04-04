<?php
namespace Ahy\ThemeCustomization\Observer;

use Webkul\Marketplace\Observer\SalesOrderPlaceAfterObserver as OriginalObserver;

class CustomSalesOrderPlaceAfterObserver extends OriginalObserver
{
    /**
     * Order Place Operation method.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param int                        $lastOrderId
     */
    public function orderPlacedOperations($order, $lastOrderId)
    {
        $this->productSalesCalculation($order);

        /*send placed order mail notification to seller*/

        $paymentCode = '';
        if ($order->getPayment()) {
            $paymentCode = $order->getPayment()->getMethod();
        }

        $shippingInfo = '';
        $shippingDes = '';

        $billingId = $order->getBillingAddress()->getId();

        $billaddress = $this->orderAddressFactory->create()->load($billingId);
        $billinginfo = $billaddress['firstname'].'<br/>'.
        $billaddress['street'].'<br/>'.
        $billaddress['city'].' '.
        $billaddress['region'].' '.
        $billaddress['postcode'].'<br/>'.
        $this->countryModel->create()->load($billaddress['country_id'])->getName().'<br/>T:'.
        $billaddress['telephone'];

        $order->setOrderApprovalStatus(1)->save();

        $payment = $order->getPayment()->getMethodInstance()->getTitle();

        if ($order->getShippingAddress()) {
            $shippingId = $order->getShippingAddress()->getId();
            $address = $this->orderAddressFactory->create()->load($shippingId);
            $shippingInfo = $address['firstname'].'<br/>'.
            $address['street'].'<br/>'.
            $address['city'].' '.
            $address['region'].' '.
            $address['postcode'].'<br/>'.
            $this->countryModel->create()->load($address['country_id'])->getName().'<br/>T:'.
            $address['telephone'];
            $shippingDes = $order->getShippingDescription();
        }

        $adminStoremail = $this->_marketplaceHelper->getAdminEmailId();
        $defaultTransEmailId = $this->_marketplaceHelper->getDefaultTransEmailId();
        $adminEmail = $adminStoremail ? $adminStoremail : $defaultTransEmailId;
        $adminUsername = $this->_marketplaceHelper->getAdminName();

        $sellerOrder = $this->ordersFactory->create()
        ->getCollection()
        ->addFieldToFilter('order_id', $lastOrderId)
        ->addFieldToFilter('seller_id', ['neq' => 0]);
        foreach ($sellerOrder as $info) {
            $userdata = $this->_customerRepository->getById($info['seller_id']);
            $currencyRate = $this->_marketplaceHelper->getCurrentCurrencyRate();
            $username = $userdata->getFirstname();
            $useremail = $userdata->getEmail();

            $senderInfo = [];
            $receiverInfo = [];

            $receiverInfo = [
                'name' => $username,
                'email' => $useremail,
            ];
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $totalprice = 0;
            $totalTaxAmount = 0;
            $codCharges = 0;
            $shippingCharges = 0;
            $orderinfo = '';

            $saleslistIds = [];
            $collection1 = $this->saleslistFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $lastOrderId)
            ->addFieldToFilter('seller_id', $info['seller_id'])
            ->addFieldToFilter('parent_item_id', ['null' => 'true'])
            ->addFieldToFilter('magerealorder_id', ['neq' => 0])
            ->addFieldToSelect('entity_id');

            $saleslistIds = $collection1->getData();

            $fetchsale = $this->saleslistFactory->create()
            ->getCollection()
            ->addFieldToFilter(
                'entity_id',
                ['in' => $saleslistIds]
            );
            $fetchsale->getData();
            $fetchsale->getSellerOrderCollection();
            foreach ($fetchsale as $res) {
                // $currencyRate = $res->getCurrencyRate();
                $product = $this->_productRepository->getById($res['mageproduct_id']);

                /* product name */
                $productName = $res->getMageproName();
                $result = [];
                $result = $this->getProductOptionData($res, $result);
                $productName = $this->getProductNameHtml($result, $productName);
                /* end */
                if ($res->getProductType() == 'configurable') {
                    $configurableSalesItem = $this->saleslistFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('order_id', $lastOrderId)
                    ->addFieldToFilter('seller_id', $info['seller_id'])
                    ->addFieldToFilter('parent_item_id', $res->getOrderItemId());
                    $configurableItemArr = $configurableSalesItem->getOrderedProductId();
                    $configurableItemId = $res['mageproduct_id'];
                    if (!empty($configurableItemArr)) {
                        $configurableItemId = $configurableItemArr[0];
                    }
                    $product = $this->_productRepository->getById($configurableItemId);
                } else {
                    $product = $this->_productRepository->getById($res['mageproduct_id']);
                }

                $sku = $product->getSku();
                $orderinfo = $orderinfo."<tbody><tr>
                                <td class='item-info'>".$productName."</td>
                                <td class='item-info'>".$sku."</td>
                                <td class='item-qty'>".($res['magequantity'] * 1)."</td>
                                <td class='item-price'>".
                                $order->formatPrice($this->_marketplaceHelper
                                ->getCurrentCurrencyPrice($currencyRate, $res['magepro_price'] * $res['magequantity'])).
                                '</td>
                             </tr></tbody>';
                $totalTaxAmount = $totalTaxAmount + $res['total_tax'];
                $totalprice = $totalprice + ($res['magepro_price'] * $res['magequantity']);

                /*
                * Low Stock Notification mail to seller
                */
                if ($this->_marketplaceHelper->getlowStockNotification()) {
                    $stockRegistry = $this->_objectManager->create('Magento\CatalogInventory\Model\StockRegistry');
                    $productId = $product->getId();
                    $stockItem = $stockRegistry->getStockItem($productId);
                    $stockItemQty = $stockItem->getQty();
                    if ($stockItemQty <= $this->_marketplaceHelper->getlowStockQty()) {
                        $orderProductInfo = "<tbody class='stock-left'><tr>
                                <td class='item-info'>".$productName."</td>
                                <td class='item-info'>".$sku."</td>
                                <td class='item-qty'>".($stockItemQty * 1).'</td>
                             </tr></tbody>
                             <style>
                             tbody.stock-left td, tbody.stock-left tr, tbody.stock-left th {
                                border: 1px solid;
                                padding: 5px;
                            }
                            tbody.stock-left {
                                border: 1px solid;
                                text-align: center;
                            }
                             </style>';

                        $emailTemplateVariables = [];
                        $emailTemplateVariables['myvar1'] = $orderProductInfo;
                        $emailTemplateVariables['myvar2'] = $username;

                        $this->mpEmailHelper->sendLowStockNotificationMail(
                            $emailTemplateVariables,
                            $senderInfo,
                            $receiverInfo
                        );
                    }
                }
            }
            $shippingCharges = $info->getShippingCharges();
            $couponAmount = $info->getCouponAmount();
            $totalCod = 0;

            if ($paymentCode == 'mpcashondelivery') {
                $totalCod = $info->getCodCharges();
                $codRow = "<tr class='subtotal'>
                            <th colspan='3'>".__('Cash On Delivery Charges')."</th>
                            <td colspan='3'><span>".
                            $order->formatPrice($this->_marketplaceHelper
                            ->getCurrentCurrencyPrice($currencyRate, $totalCod)).
                            '</span></td>
                            </tr>';
            } else {
                $codRow = '';
            }

            $orderinfo = $orderinfo."<tfoot class='order-totals'>
                                <tr class='subtotal'>
                                    <th colspan='3'>".__('Shipping & Handling Charges')."</th>
                                    <td colspan='3'><span>".
                                    $order->formatPrice($this->_marketplaceHelper
                                    ->getCurrentCurrencyPrice($currencyRate, $shippingCharges))."</span></td>
                                </tr>
                                <tr class='subtotal'>
                                    <th colspan='3'>".__('Discount')."</th>
                                    <td colspan='3'><span> -".
                                    $order->formatPrice($this->_marketplaceHelper
                                    ->getCurrentCurrencyPrice($currencyRate, $couponAmount)).
                                    "</span></td>
                                </tr>
                                <tr class='subtotal'>
                                    <th colspan='3'>".__('Tax Amount')."</th>
                                    <td colspan='3'><span>".
                                    $order->formatPrice($this->_marketplaceHelper
                                    ->getCurrentCurrencyPrice($currencyRate, $totalTaxAmount)).'</span></td>
                                </tr>'.$codRow."
                                <tr class='subtotal'>
                                    <th colspan='3'>".__('Grandtotal')."</th>
                                    <td colspan='3'><span>".
                                    $order->formatPrice($this->_marketplaceHelper->
                                    getCurrentCurrencyPrice(
                                        $currencyRate,
                                        $totalprice +
                                        $totalTaxAmount +
                                        $shippingCharges +
                                        $totalCod -
                                        $couponAmount
                                    )).'</span></td>
                                </tr></tfoot>';

            $emailTemplateVariables = [];
            if ($shippingInfo != '') {
                $isNotVirtual = 1;
            } else {
                $isNotVirtual = 0;
            }
            $emailTempVariables['myvar1'] = $order->getRealOrderId();
            $emailTempVariables['myvar2'] = $order['created_at'];
            $emailTempVariables['myvar4'] = $billinginfo;
            $emailTempVariables['myvar5'] = $payment;
            $emailTempVariables['myvar6'] = $shippingInfo;
            $emailTempVariables['isNotVirtual'] = $isNotVirtual;
            $emailTempVariables['myvar9'] = $shippingDes;
            $emailTempVariables['myvar8'] = $orderinfo;
            $emailTempVariables['myvar3'] = $username;

            if ($this->_marketplaceHelper->getOrderApprovalRequired()) {
                $emailTempVariables['seller_id'] = $info['seller_id'];
                $emailTempVariables['order_id'] = $lastOrderId;
                $emailTempVariables['sender_name'] = $senderInfo['name'];
                $emailTempVariables['sender_email'] = $senderInfo['email'];
                $emailTempVariables['receiver_name'] = $receiverInfo['name'];
                $emailTempVariables['receiver_email'] = $receiverInfo['email'];

                $orderPendingMailsCollection = $this->orderPendingMailsFactory->create();
                $orderPendingMailsCollection->setData($emailTempVariables);
                $orderPendingMailsCollection->setCreatedAt($this->_date->gmtDate());
                $orderPendingMailsCollection->setUpdatedAt($this->_date->gmtDate());
                $orderPendingMailsCollection->save();
                $order->setOrderApprovalStatus(0)->save();
            } else {
                $this->mpEmailHelper->sendPlacedOrderEmail(
                    $emailTempVariables,
                    $senderInfo,
                    $receiverInfo
                );
            }
        }
    }
}
