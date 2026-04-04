<?php
namespace Ahy\EfflApiIntegration\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class OneZero implements OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => '1'],
            ['value' => 0, 'label' => '0'],
        ];
    }
}
