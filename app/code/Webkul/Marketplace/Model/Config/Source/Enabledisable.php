<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Model\Config\Source;

/**
 * Source model for element with enable and disable variants.
 */
class Enabledisable implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Value which equal Enable for Enabledisable dropdown.
     */
    public const ENABLE_VALUE = 1;

    /**
     * Value which equal Disable for Enabledisable dropdown.
     */
    public const DISABLE_VALUE = 0;

    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ENABLE_VALUE, 'label' => __('Enabled')],
            ['value' => self::DISABLE_VALUE, 'label' => __('Disabled')],
        ];
    }
}
