<?php

namespace Ahy\EstateApiIntegration\Block;

use Magento\Framework\View\Element\Template;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Psr\Log\LoggerInterface;

class CheckoutInterceptor extends Template
{
    protected $checkoutSession;
    protected $urlEncoder;
    protected $urlInterface;
    protected $customerSession;
    protected $logger;

    public function __construct(
        Template\Context $context,
        CheckoutSession $checkoutSession,
        EncoderInterface $urlEncoder,
        UrlInterface $urlInterface,
        CustomerSession $customerSession,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->urlEncoder = $urlEncoder;
        $this->urlInterface = $urlInterface;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    public function getCartProductIds()
    {
        $cart = $this->checkoutSession->getQuote();
        $productIds = [];

        foreach ($cart->getAllVisibleItems() as $item) {
            $productId = $item->getProduct()->getId();
            $this->logger->debug('EstateInterceptor Product ID: ' . $productId);
            $productIds[] = $productId;
        }

        return $productIds;
    }

    public function getZipCode()
    {
        $cart = $this->checkoutSession->getQuote();
        $shippingAddress = $cart->getShippingAddress();
        $billingAddress = $cart->getBillingAddress();

        $zip = $shippingAddress && $shippingAddress->getPostcode()
            ? $shippingAddress->getPostcode()
            : ($billingAddress && $billingAddress->getPostcode()
                ? $billingAddress->getPostcode()
                : '');

        if (!$zip && $this->customerSession->isLoggedIn()) {
            $customer = $this->customerSession->getCustomer();
            $defaultShipping = $customer->getDefaultShippingAddress();
            if ($defaultShipping) {
                $zip = $defaultShipping->getPostcode();
            }
        }

        $this->logger->debug('EstateInterceptor ZIP Code: ' . $zip);
        return $zip;
    }
}
