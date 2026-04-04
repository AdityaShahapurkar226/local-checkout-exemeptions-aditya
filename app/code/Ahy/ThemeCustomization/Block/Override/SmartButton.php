<?php
namespace Ahy\ThemeCustomization\Block\Override;

use Magento\Paypal\Block\Express\InContext\Minicart\SmartButton as OriginalSmartButton;

class SmartButton extends OriginalSmartButton
{

    protected $session;
    protected $serializer; // Define the serializer property
    protected $urlBuilder; // Define the urlBuilder property
    protected $smartButtonConfig; // Define the urlBuilder property

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Paypal\Model\ConfigFactory $configFactory
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Payment\Model\MethodInterface $payment
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Paypal\Model\SmartButtonConfig $smartButtonConfig
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Quote\Model\QuoteIdToMaskedQuoteId $quoteIdToMaskedQuoteId
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Paypal\Model\ConfigFactory $configFactory,
        \Magento\Checkout\Model\Session $session,
        \Magento\Payment\Model\MethodInterface $payment,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Paypal\Model\SmartButtonConfig $smartButtonConfig,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Quote\Model\QuoteIdToMaskedQuoteId $quoteIdToMaskedQuoteId,
        array $data = []
    ) {
        parent::__construct($context, $configFactory, $session, $payment, $serializer, $smartButtonConfig, $urlBuilder, $quoteIdToMaskedQuoteId, $data);
        $this->serializer = $serializer;
        $this->urlBuilder = $urlBuilder; 
        $this->smartButtonConfig = $smartButtonConfig; 
        $this->session = $session; 
    }
    /**
     * Returns string to initialize js component
     *
     * @return string
     */
    public function getJsInitParams(): string
    {
        $config = ['Magento_Paypal/js/in-context/button' => []];
        if($this->getQuoteId() === null){
            $quoteId = "";
        } else {
            $quoteId = $this->getQuoteId();
        }
        // if (!empty($quoteId)) {
            $clientConfig = [
                'quoteId' => $quoteId,
                'customerId' => $this->session->getQuote()->getCustomerId(),
                'button' => 1,
                'getTokenUrl' => $this->urlBuilder->getUrl(
                    'paypal/express/getTokenData',
                    ['_secure' => $this->getRequest()->isSecure()]
                ),
                'onAuthorizeUrl' => $this->urlBuilder->getUrl(
                    'paypal/express/onAuthorization',
                    ['_secure' => $this->getRequest()->isSecure()]
                ),
                'onCancelUrl' => $this->urlBuilder->getUrl(
                    'paypal/express/cancel',
                    ['_secure' => $this->getRequest()->isSecure()]
                )
            ];
            $smartButtonsConfig = $this->getIsShoppingCart()
                ? $this->smartButtonConfig->getConfig('cart')
                : $this->smartButtonConfig->getConfig('mini_cart');
            $clientConfig = array_replace_recursive($clientConfig, $smartButtonsConfig);
            $config = [
                'Magento_Paypal/js/in-context/button' => [
                    'clientConfig' => $clientConfig
                ]
            ];
        // }
        $json = $this->serializer->serialize($config);
        return $json;
    }
}
