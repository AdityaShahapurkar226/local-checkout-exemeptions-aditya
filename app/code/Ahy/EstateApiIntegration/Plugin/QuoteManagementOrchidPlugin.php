<?php

declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Plugin;

use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Ahy\EstateApiIntegration\Logger\Logger;


class QuoteManagementOrchidPlugin
{
    private Logger $logger;     
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    public function afterSubmit(
        QuoteManagement $subject,
        ?OrderInterface $order,
        Quote $quote
    ): ?OrderInterface {

        // $this->logger->info('[Orchid][Plugin] QuoteManagementOrchidPlugin triggered');

        if (!$quote || !$quote->getId()) {
            // $this->logger->warning('[Orchid][Plugin] Quote is missing or invalid');
            return $order;
        }

        if (!$order || !$order->getEntityId()) {
            // $this->logger->warning('[Orchid][Plugin] Order is missing or invalid');
            return $order;
        }

        // $this->logger->info(sprintf(
        //     '[Orchid][Plugin] Quote ID: %d | Order ID: %d | Increment ID: %s',
        //     $quote->getId(),
        //     $order->getEntityId(),
        //     $order->getIncrementId()
        // ));

        /**
         * ----------------------------------------------------
         * Copy QUOTE → ORDER
         * ----------------------------------------------------
         */
        $quoteLevel = $quote->getData('orchid_restriction_level');

        if ($quoteLevel !== null) {
            $order->setData('orchid_restriction_level', $quoteLevel);

            // $this->logger->info(sprintf(
            //     '[Orchid][Plugin][Order] Restriction copied: %s',
            //     $quoteLevel
            // ));
        } else {
            // $this->logger->info('[Orchid][Plugin][Order] No restriction found on quote');
        }

        /**
         * ----------------------------------------------------
         * Copy QUOTE ITEM → ORDER ITEM
         * ----------------------------------------------------
         */
        // $this->logger->info('[Orchid][Plugin] Copying restriction level to order items');

        $quoteItems = $quote->getAllVisibleItems();
        $orderItems = $order->getAllItems();

        foreach ($orderItems as $orderItem) {

            // Skip free gift items (price = 0)
            if ((float)$orderItem->getPrice() === 0.0) {
                // $this->logger->info(sprintf(
                //     '[Orchid][Plugin][OrderItem] Skipped free gift | OrderItemID:%d | SKU:%s',
                //     $orderItem->getItemId(),
                //     $orderItem->getSku()
                // ));
                continue;
            }

            $quoteItemId = $orderItem->getQuoteItemId();
            if (!$quoteItemId) {
                continue;
            }

            foreach ($quoteItems as $quoteItem) {
                if ((int)$quoteItem->getId() === (int)$quoteItemId) {

                    $existing = $quoteItem->getData('orchid_restriction_level');

                    if ($existing !== null) {
                        $orderItem->setData('orchid_restriction_level', $existing);

                        // $this->logger->info(sprintf(
                        //     '[Orchid][Plugin][OrderItem] OrderItemID:%d | SKU:%s | QuoteItemID:%d | Restriction:%s',
                        //     $orderItem->getItemId(),
                        //     $orderItem->getSku(),
                        //     $quoteItemId,
                        //     $existing
                        // ));
                    } else {
                        // $this->logger->info(sprintf(
                        //     '[Orchid][Plugin][OrderItem] OrderItemID:%d | SKU:%s | No restriction on quote item',
                        //     $orderItem->getItemId(),
                        //     $orderItem->getSku()
                        // ));
                    }

                    break;
                }
            }
        }

        // $this->logger->info('[Orchid][Plugin] QuoteManagementOrchidPlugin finished successfully');

        return $order;
    }
}
