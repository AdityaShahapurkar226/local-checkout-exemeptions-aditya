<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AdminLoginAsVendor
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AdminLoginAsVendor\Model;

use Webkul\AdminLoginAsVendor\Model\ResourceModel\Loginattempt as ResourceModel;

class Loginattempt extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Construct function
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
