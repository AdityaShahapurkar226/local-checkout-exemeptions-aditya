<?php

declare(strict_types=1);

namespace Ahy\Authorizenet\ViewModel\Checkout\Payment;

use Hyva\Checkout\ViewModel\Checkout\Payment\MethodList as CoreMethodList;

class MethodList extends CoreMethodList
{
    public function getList(): array
    {
        $methods = parent::getList();

        /**
         * Desired checkout order
         */
        $priority = [
            'paypal_express',
            'braintree_googlepay',
            'braintree_applepay',
            'braintree_venmo',
            'authnetahypayment',
            'customervaultcards',
        ];

        usort($methods, function ($a, $b) use ($priority) {
            // Safety guard (very important)
            if (!method_exists($a, 'getCode') || !method_exists($b, 'getCode')) {
                return 0;
            }

            $aPos = array_search($a->getCode(), $priority, true);
            $bPos = array_search($b->getCode(), $priority, true);

            $aPos = $aPos === false ? 999 : $aPos;
            $bPos = $bPos === false ? 999 : $bPos;

            return $aPos <=> $bPos;
        });

        return $methods;
    }
}
