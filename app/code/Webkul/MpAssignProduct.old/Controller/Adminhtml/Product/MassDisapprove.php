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

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpAssignProduct\Model\ResourceModel\Items\CollectionFactory;
use Webkul\MpAssignProduct\Model\Items;

/**
 * Class MassDisapprove is used to disapproved to the assigned products
 */
class MassDisapprove extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var \Webkul\MpAssignProduct\Helper\Data
     */
    protected $_assignHelper;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $productAction;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param \Webkul\MpAssignProduct\Helper\Data $helper
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Action $productAction
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Webkul\MpAssignProduct\Helper\Data $helper,
        CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Action $productAction,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->_filter = $filter;
        $this->_assignHelper = $helper;
        $this->_collectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->productAction = $productAction;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        $ids = [];
        $productIds = [];
        $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
        foreach ($collection as $assignProduct) {
            $ids[] = $assignProduct->getId();
            $productIds[] = $assignProduct->getAssignProductId();
        }
        $allStores = $this->storeManager->getStores();
        if (!empty($productIds)) {
            foreach ($allStores as $store) {
                $this->productAction->updateAttributes($productIds, ['status' => $status], $store->getId());
            }
            $this->productAction->updateAttributes($productIds, ['status' => $status], 0);
        }
        $update = ['status' => Items::STATUS_DISABLED];
        $where = ['id IN (?)' => $ids];
        try {
            $this->connection->beginTransaction();
            $this->connection->update($this->resource->getTableName(Items::TABLE_NAME), $update, $where);
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
        }
        foreach ($collection as $assignProduct) {
            $this->_assignHelper->sendStatusMail($assignProduct, 1);
        }
        $msg = 'A total of %1 Product(s) have been disapproved.';
        $this->messageManager->addSuccess(__($msg, $collection->getSize()));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpAssignProduct::product');
    }
}
