<?php
namespace Ahy\Ffl\Plugin\Order;
use Psr\Log\LoggerInterface;

class SaveCustomAttribute
{
    protected $quoteRepository;
    protected $logger;

    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        LoggerInterface $logger
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->logger = $logger;
    }

    public function beforePlace(
        \Magento\Sales\Model\Order $order
    ) {
        // Get quote ID from the order
        $quoteId = $order->getQuoteId();
        
        if ($quoteId) {
            try {
                // Use repository to get the Quote by ID
                $quote = $this->quoteRepository->get($quoteId);
                $extensionAttributes = $quote->getExtensionAttributes();

                 // Check if attributes are set, if not, provide a default value
                $fflCentre = $quote->getFflCentre() ?? 'NA';
                $ageVerified = $quote->getAgeVerified() ?? 0;
                $ageOfPurchaser = $quote->getAgeOfPurchaser() ?? 0;

                $order->setFflCentre($fflCentre);
                $order->setAgeVerified($ageVerified);
                $order->setAgeOfPurchaser($ageOfPurchaser);

            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                // Handle the case when there is no such Quote
            }
        }

        // No need to return anything
    }
}