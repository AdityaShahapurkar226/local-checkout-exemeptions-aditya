<?php
namespace Ewave\ExtendedBundleProduct\Model\Config\Source;

class Boolean extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['label' => __('No'), 'value' => self::VALUE_NO],
                ['label' => __('Yes'), 'value' => self::VALUE_YES],
            ];
        }
        return $this->_options;
    }
}
