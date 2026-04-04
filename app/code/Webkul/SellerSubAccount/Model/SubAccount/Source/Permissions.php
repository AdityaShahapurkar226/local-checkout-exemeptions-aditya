<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Model\SubAccount\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 */
class Permissions implements OptionSourceInterface
{
    /**
     * @var \Webkul\SellerSubAccount\Helper\Data
     */
    public $subAccountHelper;

    /**
     * Constructor
     *
     * @param \Webkul\SellerSubAccount\Model\SubAccount $subAccountHelper
     */
    public function __construct(\Webkul\SellerSubAccount\Helper\Data $subAccountHelper)
    {
        $this->subAccountHelper = $subAccountHelper;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->subAccountHelper->getAllPermissionTypes();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
