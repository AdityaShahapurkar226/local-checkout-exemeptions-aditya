<?php
namespace Ewave\ExtendedBundleProduct\Model\Config\Source\Bundle;

use Magento\Catalog\Model\Product\Attribute\Source\Boolean;

/**
 * Class CountSeparatelyOption
 * @package Ewave\ExtendedBundleProduct\Model\Config\Source\Bundle
 */
class CountSeparatelyOption extends Boolean
{
    /**
     * Retrieve all attribute options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('Use config'), 'value' => static::VALUE_USE_CONFIG],
                ['label' => __('Yes'), 'value' => static::VALUE_YES],
                ['label' => __('No'), 'value' => static::VALUE_NO],
            ];
        }
        return $this->_options;
    }
}
