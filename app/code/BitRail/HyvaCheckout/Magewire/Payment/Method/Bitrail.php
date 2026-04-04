<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2022-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

declare(strict_types=1);

namespace Bitrail\HyvaCheckout\Magewire\Payment\Method;

use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Magewirephp\Magewire\Component;

class Bitrail extends Component implements EvaluationInterface
{
    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    {
        // Create a validation instruction for the frontend.
        $validate = $resultFactory->createValidation('bitrail-checkout');
        // A best practice to always return a Batch so others can add or remove things with a after plugin.
        return $resultFactory->createBatch()->push($validate);
    }
}