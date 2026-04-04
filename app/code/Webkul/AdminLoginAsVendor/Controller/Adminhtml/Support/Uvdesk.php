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
namespace Webkul\AdminLoginAsVendor\Controller\Adminhtml\Support;

use Magento\Backend\App\Action;

class Uvdesk extends Action
{
    /**
     * Support Userguide Link.
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl("https://webkul.uvdesk.com/en/customer/create-ticket/");
        return $resultRedirect;
    }
}
