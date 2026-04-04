<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Controller\Adminhtml\Product;

use Webkul\MpAssignProduct\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Controller\ResultFactory;

class Edit extends ProductController
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var \Webkul\MpAssignProduct\Model\ItemsFactory
     */
    protected $_items;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\MpAssignProduct\Model\ItemsFactory $items
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Webkul\MpAssignProduct\Model\ItemsFactory $items
    ) {
        $this->_backendSession = $context->getSession();
        $this->_registry = $registry;
        $this->_items = $items;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $assignProduct = $this->_items->create();
        if ($this->getRequest()->getParam('id')) {
            $assignProduct->load($this->getRequest()->getParam('id'));
        }
        if (!$assignProduct->getId()) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $this->messageManager->addError("Product not found.");
            return $resultRedirect->setPath('*/*/');
        }
        $data = $this->_backendSession->getFormData(true);
        if (!empty($data)) {
            $assignProduct->setData($data);
        }
        $this->_registry->register('mpassignproduct_product', $assignProduct);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_MpAssignProduct::MpAssignProduct');
        $resultPage->getConfig()->getTitle()->prepend(__('Product'));
        $resultPage->getConfig()->getTitle()->prepend(
            $assignProduct->getId() ? $assignProduct->getTitle() : __('New Image')
        );
        $block = \Webkul\MpAssignProduct\Block\Adminhtml\Product\Edit::class;
        $content = $resultPage->getLayout()->createBlock($block);
        $resultPage->addContent($content);
        return $resultPage;
    }
}
