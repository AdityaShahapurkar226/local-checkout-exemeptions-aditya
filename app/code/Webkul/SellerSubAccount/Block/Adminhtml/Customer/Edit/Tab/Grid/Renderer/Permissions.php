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
namespace Webkul\SellerSubAccount\Block\Adminhtml\Customer\Edit\Tab\Grid\Renderer;

class Permissions extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
     * Renders grid column
     *
     * @param   \Magento\Framework\DataObject $row
     * @return  string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $value = explode(",", $row->getPermissionType());
        $arr = [];
        $allOptions = $this->subAccountHelper->getAllPermissionTypes();
        foreach ($value as $key => $val) {
            if (!empty($allOptions[$val])) {
                array_push($arr, $allOptions[$val]);
            }
        }
        return implode(',', $arr);
    }
}
