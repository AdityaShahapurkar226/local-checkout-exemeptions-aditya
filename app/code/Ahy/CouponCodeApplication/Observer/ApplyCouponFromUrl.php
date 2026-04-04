<?php

namespace Ahy\CouponCodeApplication\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\Request\Http;
use Magento\Checkout\Model\Cart;

class ApplyCouponFromUrl implements ObserverInterface
{
    protected $request;
    protected $cart;

    public function __construct(
        Http $request,
        Cart $cart
    ) {
        $this->request = $request;
        $this->cart = $cart;
    }

    public function execute(Observer $observer)
    {
        $couponCode = $this->request->getParam('coupon');

        if ($couponCode) {
            // Apply the coupon to the cart if it's valid
            $this->cart->getQuote()->setCouponCode($couponCode)->collectTotals()->save();
        }
    }
}
