<?php

namespace Ahy\ThemeCustomization\Block\Paypal;
use Magento\Checkout\Model\Session as CheckoutSession;
use Ahy\ThemeCustomization\Helper\Data;
use Psr\Log\LoggerInterface;

class ShowPaypal extends \Magento\Framework\View\Element\Template
{
    protected $smartButton;
    protected $checkoutSession;
    protected $ahyHelper;
    protected $logger;
    
    public function __construct(
        \Magento\Paypal\Block\Express\InContext\Minicart\SmartButton $smartButton,
        \Magento\Framework\View\Element\Template\Context $context,
        CheckoutSession $checkoutSession,
        Data $ahyHelper,
        LoggerInterface $logger,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->ahyHelper = $ahyHelper;
        $this->smartButton = $smartButton;
        $this->logger = $logger;
    }

    public function getWidgetJson()
    {
        $checkForTheRestrictedProduct = $this->checkForTheRestrictedItemsInCart();
        if($checkForTheRestrictedProduct){
            $widgetData = json_decode($this->smartButton->getJsInitParams(), true);
            if (isset($widgetData['Magento_Paypal/js/in-context/button'])) {
                return json_encode($widgetData['Magento_Paypal/js/in-context/button']);
            }
        }
        return '';
    }

    public function checkForTheRestrictedItemsInCart(){
        $quote = $this->checkoutSession->getQuote();
        $isFflRequired = false;
        $isAgeVerificationRequired = false;
        foreach ($quote->getAllItems() as $item) {
            try {
                $product        = $item->getProduct();
                $productId      = $product->getId();
                $isFflRequired  = $this->ahyHelper->getProductAttribute($productId, 'ffl_selection_required');
                $isAgeVerificationRequired = $this->ahyHelper->getProductAttribute($productId, 'age_verification_required');
                if ($isAgeVerificationRequired || $isFflRequired) {
                    break;
                }                
            } catch (\Exception $e) {
                $product = $item->getProduct();
                $productName = $product ? $product->getName() : 'Unknown Product';
                $this->logger->error("Error occurred for product: $productName. Exception: " . $e->getMessage());
            }
        }
        if (($isAgeVerificationRequired || $isFflRequired)){
            return false;
        }
        return true;
    }
}
