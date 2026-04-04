<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2022-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Ahy\ThemeCustomization\Magewire\Checkout\Shipping;

use Magento\Framework\Exception\LocalizedException;
use Hyva\Checkout\Exception\CheckoutException;
use Hyva\Checkout\Magewire\Checkout\Shipping\MethodList as originalMethodList;

class MethodList extends originalMethodList
{
    public function boot(): void
    {
        try {
            $quote  = $this->sessionCheckout->getQuote();
            $method = $quote->getShippingAddress()->getShippingMethod();
            $quote->getShippingAddress()->setCollectShippingRates(true)->collectShippingRates();
            
            $shippingMethods = $quote->getShippingAddress()->getAllShippingRates();
            $availableShippingMethods = [];
            foreach ($shippingMethods as $shippingMethod) {
                $availableShippingMethods[] = $shippingMethod->getCode();
            }
            $methodName = $availableShippingMethods[0];
            $this->updateShippingMethod($methodName);
        } catch (LocalizedException $exception) {
            $method = null;
        }
        // $this->method = empty($method) ? null : $method;
        $this->method = $methodName;
    }
    
    protected function updateShippingMethod(string $methodName): void
    {
        try {
            $quote = $this->sessionCheckout->getQuote();
            $shippingAddress = $quote->getShippingAddress();
            $rate = $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates()
                ->getShippingRateByCode($methodName);
            if ($rate === false) {
                throw new CheckoutException(__('Invalid shipping method'));
            }
            if ($this->shippingMethodManagement->set($quote->getId(), $rate->getCarrier(), $rate->getMethod())) {
                // Update the selected method
                $this->method = $methodName;

                // Dispatch the necessary browser events
                $this->dispatchBrowserEvent('checkout:shipping:method-activate', ['method' => $methodName]);
            }
        } catch (CheckoutException $exception) {
            $this->dispatchErrorMessage($exception->getMessage());
        } catch (LocalizedException $exception) {
            $this->dispatchErrorMessage('Something went wrong while saving your shipping preferences.');
        }
        // Always emit the custom event to trigger frontend updates
        $this->emit('shipping_method_selected');
    }
}
