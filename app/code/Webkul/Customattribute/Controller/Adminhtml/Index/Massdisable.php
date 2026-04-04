<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Customattribute
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Customattribute\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Customattribute\Model\ResourceModel\Systemattribute\CollectionFactory;

/**
 * Customattribute mass disable.
 */
class Massdisable extends \Magento\Backend\App\Action
{

    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;
    
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;
    /**
     * @var status
     */
    protected $_status = 0;

    /**
     * @param Context                                               $context
     * @param Filter                                                $filter
     * @param \Magento\Framework\Stdlib\DateTime\DateTime           $date
     * @param \Magento\Framework\Stdlib\DateTime                    $dateTime
     * @param \Magento\Store\Model\StoreManagerInterface            $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface       $productRepository
     * @param CollectionFactory                                     $collectionFactory
     * @param \Webkul\Customattribute\Model\ManageattributeFactory  $attributeManagerFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory,
        \Webkul\Customattribute\Model\ManageattributeFactory $attributeManagerFactory
    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->_date = $date;
        $this->_storeManager = $storeManager;
        $this->_productRepository = $productRepository;
        $this->_dateTime = $dateTime;
        $this->_attributeManagerFactory = $attributeManagerFactory;
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
        $selected = $this->getRequest()->getParam('selected');
        $excluded = $this->getRequest()->getParam('excluded');
        $ids = [];
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        foreach ($collection as $item) {
            $ids[] = $item->getId();
        }
        if ($excluded == 'false') {
            $excluded = [];
        }
        if (!empty($selected)) {
            $ids = array_intersect($ids, $selected);
            ;
        } elseif (!empty($excluded)) {
            $ids = array_diff($ids, $excluded);
        }
        foreach ($ids as $id) {
            $collection = $this->_attributeManagerFactory->create()
                ->getCollection()->addFieldToFilter('attribute_id', $id);
            if (!empty($collection) && $collection->getSize() > 0) {
                foreach ($collection as $row) {
                    $row->setStatus($this->_status);
                    $row->save();
                }
            } else {
                $querydata1 = $this->_attributeManagerFactory->create();
                $querydata1->setAttributeId($id);
                $querydata1->setStatus($this->_status);
                $querydata1->save();
            }
        }
        
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been disabled.', count($ids)));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
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
        return $this->_authorization->isAllowed('Webkul_Customattribute::customattribute');
    }
}
