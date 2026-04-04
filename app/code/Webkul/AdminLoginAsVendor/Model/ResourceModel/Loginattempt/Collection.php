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
namespace Webkul\AdminLoginAsVendor\Model\ResourceModel\Loginattempt;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Webkul\AdminLoginAsVendor\Model\ResourceModel\Loginattempt as ResourceModel;
use Webkul\AdminLoginAsVendor\Model\Loginattempt as Model;

class Collection extends AbstractCollection
{
   
    /**
     * Constructor function
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
