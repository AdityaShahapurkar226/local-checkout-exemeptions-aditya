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
namespace Webkul\AdminLoginAsVendor\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * Return Module enable/disable  Value
     *
     * @return boolean
     */
    public function isEnable()
    {
        return $this->scopeConfig
        ->getValue('adminloginasvendor/general_settings/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
