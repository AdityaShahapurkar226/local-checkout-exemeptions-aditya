<?php
declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ahy\EstateApiIntegration\Logger\Logger;
use Ahy\EstateApiIntegration\Model\OrchidRestrictionAggregator;

class CopyRestrictionToQuoteItemObserver implements ObserverInterface
{
    private OrchidRestrictionAggregator $aggregator;
    private Logger $logger; 

    public function __construct(
        OrchidRestrictionAggregator $aggregator,
        Logger $logger
    ) {
        $this->aggregator = $aggregator;
        $this->logger = $logger;
    }
public function execute(Observer $observer): void
{
    /** @var \Magento\Quote\Model\Quote\Item $item */
    $item = $observer->getEvent()->getQuoteItem();
    if (!$item || !$item->getQuote()) {
        return;
    }

    // 🔒 Prevent multiple executions
    if ($item->getData('orchid_restriction_applied')) {
        return;
    }

    $quote = $item->getQuote();
    $quoteLevel = $quote->getData('orchid_restriction_level');

    if (!$quoteLevel) {
        return;
    }

    $product = $item->getProduct();
    if ($product && $product->getData('is_free_gift')) {
        return;
    }

    $existing = $item->getData('orchid_restriction_level');
    $final = $this->aggregator->aggregate($existing, $quoteLevel);

    $item->setData('orchid_restriction_level', $final);
    $item->setData('orchid_restriction_applied', 1);
}

}
