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

/**
 * Class Query MassDelete
 */
class MassDelete extends \Magento\Backend\App\Action
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
     * Constructor
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ConversationCollectionFactory $conversationCollectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ConversationCollectionFactory $conversationCollectionFactory
    ) {

        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->conversationCollectionFactory = $conversationCollectionFactory;
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
        $comm_id = [];
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $count_record = $collection->getSize();
        foreach ($collection as $item) {
            $comm_id[] = $item->getId();
        }
        $collection->walk('delete');
        
        $collection_data = $this->conversationCollectionFactory->create()
            ->addFieldToFilter('comm_id', ['in' => $comm_id]);
        $collection_data->walk('delete');

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $count_record));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('mpsellerbuyercommunication/query/index/');
    }

    /**
     * Delete Item
     *
     * @param collection $item
     * @return void
     */
    public function deleteItem($item)
    {
        $item->delete();
    }

    /**
     * Delete Conversation
     *
     * @param object $value
     * @return void
     */
    public function deleteConversation($value)
    {
        $value->delete();
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
