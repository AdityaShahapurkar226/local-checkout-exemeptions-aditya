<?php

namespace BitRail\PaymentGateway\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\App\State;

class PaymentEnvironment implements ArrayInterface
{
    /**
     * @var State
     */
    private $appState;

    public function __construct(State $appState)
    {
        $this->appState = $appState;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $array = [
            [
                'value' => 'prod',
                'label' => 'Production',
            ],
            [
                'value' => 'sandbox',
                'label' => 'Sandbox',
            ],
        ];
        if ($this->appState->getMode() === State::MODE_DEVELOPER) {
            $array[] = [
                'value' => 'qa',
                'label' => 'QA',
            ];
        }

        return $array;
    }
}
