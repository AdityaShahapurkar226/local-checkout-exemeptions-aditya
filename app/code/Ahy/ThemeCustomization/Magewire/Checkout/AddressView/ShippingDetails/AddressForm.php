<?php

namespace Ahy\ThemeCustomization\Magewire\Checkout\AddressView\ShippingDetails;

use Hyva\Checkout\Magewire\Checkout\AddressView\ShippingDetails\AddressForm as OriginalAddressForm;

class AddressForm extends OriginalAddressForm
{
    public bool $klaviyoOptIn = false;

    public function mount() {
        $this->klaviyoOptIn = $this->sessionCheckout->getData('klaviyoOptIn') ?? false;
    }

    public function updatedKlaviyoOptIn(bool $value): bool
    {
        $this->klaviyoOptIn = $value;
        $this->sessionCheckout->setData('klaviyoOptIn', $this->klaviyoOptIn);
        return $value;
    }
}
