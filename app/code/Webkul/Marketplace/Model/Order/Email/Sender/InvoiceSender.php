<?php
declare(strict_types=1);

namespace Webkul\Marketplace\Model\Order\Email\Sender;

use Magento\Sales\Model\Order\Invoice;
use Magento\Framework\DataObject;
use Magento\Sales\Model\Order;

class InvoiceSender extends \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
{
    /**
     * Send invoice
     *
     * @param Invoice $invoice
     * @param boolean $forceSyncMode
     */
    public function send(Invoice $invoice, $forceSyncMode = false)
    {
        $this->identityContainer->setStore($invoice->getStore());
        $invoice->setSendEmail($this->identityContainer->isEnabled());

        if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
            $sellerOrder = $invoice->getOrder();
            if ($this->checkIfPartialInvoice($sellerOrder, $invoice)) {
                $sellerOrder->setBaseSubtotal((float) $invoice->getBaseSubtotal());
                $sellerOrder->setBaseTaxAmount((float) $invoice->getBaseTaxAmount());
                //$sellerOrder->setBaseShippingAmount((float) $invoice->getBaseShippingAmount());
            }

            $transport = [
                'order' => $sellerOrder,
                'order_id' => $sellerOrder->getId(),
                'invoice' => $invoice,
                'invoice_id' => $invoice->getId(),
                'comment' => $invoice->getCustomerNoteNotify() ? $invoice->getCustomerNote() : '',
                'billing' => $sellerOrder->getBillingAddress(),
                'payment_html' => $this->getPaymentHtml($sellerOrder),
                'store' => $sellerOrder->getStore(),
                'formattedShippingAddress' => $this->getFormattedShippingAddress($sellerOrder),
                'formattedBillingAddress' => $this->getFormattedBillingAddress($sellerOrder),
                'order_data' => [
                    'customer_name' => $sellerOrder->getCustomerName(),
                    'is_not_virtual' => $sellerOrder->getIsNotVirtual(),
                    'email_customer_note' => $sellerOrder->getEmailCustomerNote(),
                    'frontend_status_label' => $sellerOrder->getFrontendStatusLabel()
                ]
            ];
            $transportObjectData = new DataObject($transport);

            /**
             * Event argument `transport` is @deprecated. Use `transportObjectData` instead.
             */
            $this->eventManager->dispatch(
                'email_invoice_set_template_vars_before',
                [
                    'sender' => $this,
                    'transport' => $transportObjectData->getData(),
                    'transportObject' => $transportObjectData
                ]
            );

            $this->templateContainer->setTemplateVars($transportObjectData->getData());

            if ($this->checkAndSend($sellerOrder)) {
                $invoice->setEmailSent(true);
                $this->invoiceResource->saveAttribute(
                    $invoice,
                    ['send_email', 'email_sent']
                );
                return true;
            }
        } else {
            $invoice->setEmailSent(null);
            $this->invoiceResource->saveAttribute(
                $invoice,
                'email_sent'
            );
        }

        $this->invoiceResource->saveAttribute(
            $invoice,
            'send_email'
        );

        return false;
    }

    /**
     * Check if the order contains partial invoice
     *
     * @param Order $sellerOrder
     * @param Invoice $invoice
     * @return bool
     */
    private function checkIfPartialInvoice(Order $sellerOrder, Invoice $invoice): bool
    {
        $totalQtyOrdered = (float) $sellerOrder->getTotalQtyOrdered();
        $totalQtyInvoiced = (float) $invoice->getTotalQty();
        return $totalQtyOrdered !== $totalQtyInvoiced;
    }
}
