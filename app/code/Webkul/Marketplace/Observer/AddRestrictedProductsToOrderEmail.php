<?php

declare(strict_types=1);

namespace Webkul\Marketplace\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\CartRepositoryInterface;

class AddRestrictedProductsToOrderEmail implements ObserverInterface
{
    private CartRepositoryInterface $quoteRepository;

    public function __construct(
        CartRepositoryInterface $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    public function execute(Observer $observer): void
    {
        $transport = $observer->getEvent()->getTransportObject()
            ?: $observer->getEvent()->getTransport();

        if (!$transport || !$transport->getOrder()) {
            return;
        }

        $order = $transport->getOrder();
        $quoteId = (int) $order->getQuoteId();

        if (!$quoteId) {
            return;
        }

        try {
            $quote = $this->quoteRepository->get($quoteId);
        } catch (\Exception $e) {
            return;
        }

        $restrictedProducts = [];
        $hasRestrictedProducts = false;

        foreach ($quote->getAllVisibleItems() as $item) {

            // Skip free gifts
            if ((float)$item->getPrice() <= 0 || stripos($item->getSku(), 'FREE') === 0) {
                continue;
            }

            $restrictionLevel = $item->getData('orchid_restriction_level');

            // Only add if level is not null or empty
            if ($restrictionLevel !== null && $restrictionLevel !== '' && $restrictionLevel !== '1') {
                $hasRestrictedProducts = true;
                $restrictedProducts[] = $item->getName();
            }
        }

        // Inject email vars
        $transport->setData('has_restricted_products', $hasRestrictedProducts);
        $transport->setData(
            'restricted_message',
            $hasRestrictedProducts
                ? 'This order contains restricted item(s) and has been placed on hold for additional verification.'
                : ''
        );

        $transport->setData(
            'restricted_products',
            implode('<br/>', $restrictedProducts)
        );
    }
}
