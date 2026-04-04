<?php

declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Model;

use Ahy\EstateApiIntegration\Api\OrchidRestrictionManagementInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Ahy\EstateApiIntegration\Logger\Logger;

/**
 * Class OrchidRestrictionManagement
 * Manages saving individual item restrictions and aggregating the highest priority raw response for the quote.
 */
class OrchidRestrictionManagement implements OrchidRestrictionManagementInterface
{
    /**
     * @param CheckoutSession $checkoutSession
     * @param CartRepositoryInterface $cartRepository
     * @param OrchidRestrictionAggregator $aggregator
     * @param Logger $logger
     */
    public function __construct(
        private CheckoutSession $checkoutSession,
        private CartRepositoryInterface $cartRepository,
        private OrchidRestrictionAggregator $aggregator,
        private Logger $logger
    ) {}

    /**
     * Saves the restriction level for a specific product and re-evaluates the overall quote restriction.
     *
     * @param mixed $responseCode The raw response (array or string/numeric)
     * @param int $productId
     * @return bool
     */
    public function saveRestriction($responseCode, int $productId): bool
    {
        try {
            $quote = $this->checkoutSession->getQuote();
            if (!$quote->getId()) {
                $this->logger->info('[Orchid] No active quote found in session.');
                return false;
            }

            // Prepare the raw string to save (JSON string or raw value)
            $rawToSave = is_array($responseCode)
                ? json_encode($responseCode)
                : (string) $responseCode;

            /**
             * 1. SAVE ON ITEM LEVEL
             * Every item saves its own raw response directly.
             */
            $itemUpdated = false;
            foreach ($quote->getAllItems() as $item) {
                if ((int) $item->getProductId() === $productId) {
                    $item->setData('orchid_restriction_level', $rawToSave);
                    $itemUpdated = true;
                }
            }

            if (!$itemUpdated) {
                $this->logger->warning(sprintf('[Orchid] Product ID %d not found in quote items.', $productId));
                return false;
            }

            // Save the quote to persist item data before aggregation
            $this->cartRepository->save($quote);
            
            // Reload to ensure we have fresh data for all items
            $quote = $this->cartRepository->get($quote->getId());

            /**
             * 2. RE-EVALUATE QUOTE LEVEL (Aggregated)
             * Find the highest priority among all items.
             */
            $highestPriorityValue = null;
            $winningRawResponse = null;

            foreach ($quote->getAllItems() as $item) {
                $itemRaw = $item->getData('orchid_restriction_level');
                if ($itemRaw === null || $itemRaw === '') {
                    continue;
                }

                /**
                 * Extract the specific value for priority comparison.
                 * If JSON, use 'shipping_restriction'. Otherwise, use the raw string.
                 */
                $valueForPriority = $itemRaw;
                if ($this->isJsonObject($itemRaw)) {
                    $decoded = json_decode($itemRaw, true);
                    $valueForPriority = $decoded['shipping_restriction'] ?? $itemRaw;
                }

                // Use aggregator to determine if this item has higher priority
                $newHighest = $this->aggregator->aggregate(
                    $highestPriorityValue,
                    $valueForPriority
                );

                // If priority changed, update the winner
                if ($newHighest !== $highestPriorityValue || $winningRawResponse === null) {
                    $highestPriorityValue = $newHighest;
                    $winningRawResponse = $itemRaw;
                }
            }

            /**
             * 3. SAVE THE WINNING RAW RESPONSE ON QUOTE
             */
            if ($winningRawResponse !== null) {
                $quote->setData('orchid_restriction_level', $winningRawResponse);
                $this->cartRepository->save($quote);
                $this->logger->info(sprintf('[Orchid] Quote %d updated with winning raw response: %s', $quote->getId(), $winningRawResponse));
            }

            return true;
        } catch (\Throwable $e) {
            $this->logger->error('[Orchid] Save failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    /**
     * Stricter JSON object detection
     */
    private function isJsonObject(string $value): bool
    {
        $trimmed = trim($value);
        if (str_starts_with($trimmed, '{') && str_ends_with($trimmed, '}')) {
            json_decode($trimmed);
            return json_last_error() === JSON_ERROR_NONE;
        }
        return false;
    }
}