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
namespace Webkul\MpSellerBuyerCommunication\Block;

use Magento\Framework\View\Element\Template;
use \Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication\CollectionFactory;

/**
 * SellerBuyerCommunication block
 *
 * @author      Webkul Software
 */
class SellerBuyerCommunication extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CollectionFactory
     */
    protected $sellerBuyerCommunicationCollectionFactory;

    /**
     * @var Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogProductCollectionFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CollectionFactory $sellerBuyerCommunicationCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogProductCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CollectionFactory $sellerBuyerCommunicationCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogProductCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->sellerBuyerCommunicationCollectionFactory = $sellerBuyerCommunicationCollectionFactory;
        $this->catalogProductCollectionFactory = $catalogProductCollectionFactory;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    /**
     * Execute
     *
     * @return void
     */
    public function getsellerBuyerCommunicationCollection()
    {
        if (!($customerId = $this->customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->sellerBuyerCommunicationList) {
            $products = [];

            $filter=$this->getRequest()->getParam('s')!=""?$this->getRequest()->getParam('s'):"";

            $product_coll = $this->catalogProductCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter(
                'name',
                ['like' => "%".$filter."%"]
            );
            foreach ($product_coll as $data) {
                array_push($products, $data->getId());
            }
            if ($filter=="") {
                array_push($products, 0);
            }

            $collection = $this->sellerBuyerCommunicationCollectionFactory->create()->addFieldToSelect(
                '*'
            )
            ->addFieldToFilter(
                'customer_id',
                $customerId
            )->addFieldToFilter(
                'product_id',
                ['in' => $products]
            );
            $this->sellerBuyerCommunicationList = $collection;
        }
        return $this->sellerBuyerCommunicationList;
    }

    /**
     * Prepare Layout
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getsellerBuyerCommunicationCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'mpsellerbuyercommunication.communication.list.pager'
            )
            ->setAvailableLimit([4=>4,8=>8,16=>16])
            ->setCollection(
                $this->getsellerBuyerCommunicationCollection()
            );
            $this->setChild('pager', $pager);
            $this->getsellerBuyerCommunicationCollection()->load();
        }
        return $this;
    }

   /**
    * Get Pager Html
    *
    * @return void
    */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
