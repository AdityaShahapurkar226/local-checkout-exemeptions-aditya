<?php

declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Plugin;

use Magento\Sales\Model\Order;
use Ahy\EstateApiIntegration\Logger\Logger;

class OrderHoldBeforeSavePlugin
{
    private Logger $logger;

    private array $statusMap = [
        '4' => 'orchid_fail_restriction',
        '3' => 'compliance_hold',
        '5' => 'partially_on_hold',
        '0' => 'orchid_fail_restriction',
        'B' => 'compliance_hold',
        'A' => 'compliance_hold',
    ];

    private array $restrictionMessages = [
        '3' => "Order placed on hold due to Orchid restriction (Roster state)",
        '5' => "Order placed on hold due to Orchid restriction (Label 5)",
        '4' => "Order placed on hold due to Orchid Fail Restriction or the UPC not their in the Magento DB(Label 4)",
        '0' => "Order placed on hold due to Orchid Fail Restriction (Label 0)",
        'B' => "Order placed on hold due to Shipping restriction (B)",
    ];

    private array $shippingStatusMap = [
        'AA' => 'compliance_hold',
        'SHI' => 'compliance_hold',
        'BB' => 'compliance_hold',
        'CC' => 'compliance_hold',
        'SNJ' => 'compliance_hold'
    ];

    private array $shippingMessages = [
        'AA' => "Order placed on hold due to Shipping restriction (AA)",
        'SHI' => "Order placed on hold due to Shipping restriction (SHI)",
        'BB' => "Order placed on hold due to Shipping restriction (BB)",
        'CC' => "Order placed on hold due to Shipping restriction (CC)",
        'SNJ' => "Order placed on hold due to Shipping restriction (SNJ)"
    ];

    // States where we should NOT auto-hold (order is being processed/completed)
    private array $skipHoldStates = [
        Order::STATE_COMPLETE,
        Order::STATE_CLOSED,
        Order::STATE_CANCELED,
    ];

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function beforeSave(Order $order)
    {
        $this->logger->info("[PLUGIN] OrderHoldBeforeSavePlugin triggered for Order ID: " . $order->getIncrementId());

        // ✅ FIX 1: Skip if order is already in a terminal state
        if (in_array($order->getState(), $this->skipHoldStates, true)) {
            $this->logger->info("[PLUGIN] Order is in state {$order->getState()}, skipping hold logic.");
            return;
        }

        // ✅ FIX 2: Only apply hold if restriction level CHANGED or order is new
        $levelData = $order->getData('orchid_restriction_level');
        $origLevelData = $order->getOrigData('orchid_restriction_level');

        // If data hasn't changed and order already exists, don't re-apply holds
        if ($order->getId() && $levelData === $origLevelData) {
            $this->logger->info("[PLUGIN] Restriction level unchanged, skipping hold re-application.");
            return;
        }

        // Extract shipping restriction if JSON
        $shippingLevel = null;
        $level = null;
        
        if ($levelData) {
            $decoded = json_decode($levelData, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $shippingLevel = $decoded['shipping_restriction'] ?? null;
                $level = $decoded['restriction'] ?? null;
            } else {
                $level = (string)$levelData;
            }
        }

        // Only apply if we have actual restriction data
        if (!$level && !$shippingLevel) {
            $this->logger->info("[PLUGIN] No restriction levels found, skipping.");
            return;
        }

        $this->applyRestrictionHold($order, $level, $this->statusMap, $this->restrictionMessages, "[Orchid]");
        $this->applyRestrictionHold($order, $shippingLevel, $this->shippingStatusMap, $this->shippingMessages, "[Shipping]");
    }

    private function applyRestrictionHold(
        Order $order,
        ?string $level,
        array $statusMap,
        array $messagesMap,
        string $logPrefix = ""
    ): void {
        if (!$level) {
            return;
        }

        if (!isset($statusMap[$level])) {
            $this->logger->warning("{$logPrefix} Restriction level {$level} has no mapped status, skipping.");
            return;
        }

        $status = $statusMap[$level];
        $comment = $messagesMap[$level] ?? "Order placed on hold due to restriction {$level}";

        try {
            // ✅ FIX 3: Only hold if order can be held AND is not already held
            if ($order->canHold() && $order->getState() !== Order::STATE_HOLDED) {
                $order->hold();
                $this->logger->info("{$logPrefix} Order held successfully.");
            } else {
                $this->logger->info("{$logPrefix} Order cannot be held or already held (state: {$order->getState()}).");
            }

            // Only update status if it's different
            if ($order->getStatus() !== $status) {
                $order->setStatus($status);
                $this->logger->info("{$logPrefix} Status set to: {$status}");
            }
        } catch (\Throwable $e) {
            $this->logger->error("{$logPrefix} Exception applying hold/status: " . $e->getMessage());
        }
    }
}