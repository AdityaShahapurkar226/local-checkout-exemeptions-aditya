<?php
namespace Ahy\Ffl\Magewire\HyvaCheckout;

use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultInterface;
use Magewirephp\Magewire\Component;

class UpdateAhyFflSelection extends Component implements EvaluationInterface
{
    public $selectedFflCentreId;

    public function mount()
    {
        // Bind the Magewire event to the Magento event
        $this->listen('ahy_ffl_selection_update', function ($payload) {
            $this->selectedFflCentreId = $payload['selectedFflCentreId'];
            // You can perform any additional logic or updates here
        });
    }

    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResultInterface
    {
        return $resultFactory->createSuccess();
    }
}
?>