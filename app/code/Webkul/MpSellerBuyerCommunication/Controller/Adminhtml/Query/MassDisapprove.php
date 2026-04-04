<?php
/**
 * Webkul Software
 *
 * @category    Webkul
 * @package     Webkul_MpSellerBuyerCommunication
 * @author      Webkul
 * @copyright   Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license     https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerBuyerCommunication\Controller\Adminhtml\Query;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication\CollectionFactory;
use Webkul\MpSellerBuyerCommunication\Model\ResourceModel\Conversation\CollectionFactory as
ConversationCollectionFactory;
use Webkul\MpSellerBuyerCommunication\Model\Conversation;

/**
 * Class Query MassDelete
 */
class MassDisapprove extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ConversationCollectionFactory
     */
    protected $conversationCollectionFactory;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ConversationCollectionFactory $conversationCollectionFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ConversationCollectionFactory $conversationCollectionFactory,
        \Magento\Framework\App\ResourceConnection $resource
    ) {

        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->conversationCollectionFactory = $conversationCollectionFactory;
        $this->connection = $resource->getConnection();
        $this->resource = $resource;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $ids = [];
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        foreach ($collection as $value) {
            $ids[] = $value->getId();
        }
        $update = ['status' => Conversation::STATUS_DISAPPROVE];
        $where = ['entity_id IN (?)' => $ids];

        try {
            $this->connection->beginTransaction();
            $this->connection->update($this->resource->getTableName(Conversation::TABLE_NAME), $update, $where);
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been disapproved.', count($ids)));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('mpsellerbuyercommunication/query/index/');
    }

    /**
     * Save Status
     *
     * @param object $value
     * @param string $val
     * @return void
     */
    public function saveStatus($value, $val)
    {
        $value->setStatus($val)->save();
    }
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpSellerBuyerCommunication::query_view');
    }
}
