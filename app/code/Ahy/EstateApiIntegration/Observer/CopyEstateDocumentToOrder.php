<?php

declare(strict_types=1);

namespace Ahy\EstateApiIntegration\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class CopyEstateDocumentToOrder implements ObserverInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Copy the estate document from quote to order
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer): void
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getQuote();

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();

        $document = $quote->getData('estate_document');

        if ($document) {
            $order->setData('estate_document', $document);
            $this->logger->info('Estate document copied to order', [
                'document_path' => $document,
                'order_id' => $order->getId(),
            ]);
        }
    }
}
