<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AdminLoginAsVendor
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\AdminLoginAsVendor\Controller\Adminhtml\Support;

use Magento\Backend\App\Action;

class Returnpolicy extends Action
{
    /**
     * Support Returnpolicy Link.
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl("https://store.webkul.com/refund-policy.html/");
        return $resultRedirect;
    }
}
