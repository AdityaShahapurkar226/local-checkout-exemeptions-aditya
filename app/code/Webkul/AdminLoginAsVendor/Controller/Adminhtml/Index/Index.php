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

namespace Webkul\AdminLoginAsVendor\Controller\Adminhtml\Index;

class Index extends \Magento\Backend\App\Action
{

    /**
     * Constructor function
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
   /**
    * Execute function
    */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Login Attempts')));
        return $resultPage;
    }

     /**
      * Check for is allowed.
      *
      * @return bool
      */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_AdminLoginAsVendor::adminloginasvendor');
    }
}
