<?php
namespace Ahy\CharityAndDonation\Magewire\HyvaCheckout;

use Hyva\Checkout\Magewire\Checkout\PriceSummary as OriginalPriceSummary;

class PriceSummary extends OriginalPriceSummary
{

    protected $listeners = [
        'shipping_method_selected'  => 'refresh',
        'payment_method_selected'   => 'refresh',
        'coupon_code_applied'       => 'refresh',
        'coupon_code_revoked'       => 'refresh',
        'donation_calculated'       => 'refresh'
    ];
}
