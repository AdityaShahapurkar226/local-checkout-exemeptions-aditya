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
use Webkul\MpAssignProduct\Model\Items;

class Save extends ProductController
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @var \Webkul\MpAssignProduct\Model\ItemsFactory
     */
    protected $_items;

    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $_assignHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $productAction;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\MpAssignProduct\Model\ItemsFactory $items
     * @param \Webkul\MpAssignProduct\Helper\Data $mpAssignHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Action $productAction
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Webkul\MpAssignProduct\Model\ItemsFactory $items,
        \Webkul\MpAssignProduct\Helper\Data $mpAssignHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Action $productAction
    ) {
        $this->_backendSession = $context->getSession();
        $this->_items = $items;
        $this->_assignHelper = $mpAssignHelper;
        $this->storeManager = $storeManager;
        $this->productAction = $productAction;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($this->getRequest()->isPost()) {
            $assignId = $this->getRequest()->getParam('id');
            $requestedStatus = $this->getRequest()->getParam('product_status');
            $assignProduct = $this->_items->create();
            $assignProduct->load($assignId);
            $status = $assignProduct->getStatus();
            $type = $assignProduct->getType();
            if ($requestedStatus == 1) {
                //Approve Product
                if ($status == 0) {
                    $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
                    $allStores = $this->storeManager->getStores();
                    $assignProductId = $assignProduct->getAssignProductId();
                    if ($assignProductId) {
                        foreach ($allStores as $store) {
                            $this->productAction->updateAttributes(
                                [$assignProductId],
                                ['status' => $status],
                                $store->getId()
                            );
                        }
                        $this->productAction->updateAttributes([$assignProductId], ['status' => $status], 0);
                    }
                    $assignProduct->setStatus(Items::STATUS_ENABLED)->save();
                    $this->_assignHelper->sendStatusMail($assignProduct);
                }
            } else {
                //Disapprove Product
                if ($status == 1) {
                    $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
                    $allStores = $this->storeManager->getStores();
                    $assignProductId = $assignProduct->getAssignProductId();
                    if ($assignProductId) {
                        foreach ($allStores as $store) {
                            $this->productAction->updateAttributes(
                                [$assignProductId],
                                ['status' => $status],
                                $store->getId()
                            );
                        }
                        $this->productAction->updateAttributes(
                            [$assignProductId],
                            ['status' => $status],
                            0
                        );
                    }
                    $assignProduct->setStatus(Items::STATUS_DISABLED)->save();
                    $this->_assignHelper->sendStatusMail($assignProduct, 1);
                }
            }
            $this->messageManager->addSuccess("Status updated successfully.");
            return $resultRedirect->setPath('*/*/edit', ['_current' => true, 'id' => $assignId]);
        }
        $this->messageManager->addError("Something went wrong.");
        return $resultRedirect->setPath('*/*/');
    }
}
