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
namespace Webkul\MpSellerBuyerCommunication\Controller\Adminhtml\Query\Message;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpSellerBuyerCommunication\Model\ResourceModel\Conversation\CollectionFactory;

/**
 * Class Query Message MassDelete
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
     * Constructor
     *
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {

        $this->filter = $filter;
        $this->redirect = $redirect;
        $this->collectionFactory = $collectionFactory;
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
        $refererUrl = $this->redirect->getRefererUrl();
        $flag = 0;
        $urlArray = explode("/", $refererUrl);
        foreach ($urlArray as $value) {
            if ($flag == 1) {
                $comm_id = $value;
                break;
            } elseif ($value == 'comm_id') {
                $flag = 1;
            }
        }
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $count_record = $collection->getSize();
        
        $collection->walk('delete');

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $count_record));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('mpsellerbuyercommunication/query/view/', ['comm_id' => $comm_id]);
    }

    /**
     * Item Delete
     *
     * @param object $item
     * @return void
     */
    public function itemDelete($item)
    {
        $item->delete();
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
