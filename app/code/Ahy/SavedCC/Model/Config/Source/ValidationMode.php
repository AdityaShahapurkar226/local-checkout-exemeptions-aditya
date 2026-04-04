<?php

namespace Ahy\SavedCC\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class ValidationMode implements ArrayInterface
{
    /**
     * Options for Authorize.Net CIM Validation Mode
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'testMode', 'label' => __('Test Mode')],
            ['value' => 'liveMode', 'label' => __('Live Mode')],
        ];
    }
}
