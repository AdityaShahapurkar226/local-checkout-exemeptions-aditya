<?php
/**
 * Webkul MpSellerBuyerCommunication unit index layout
 *
 * @category    Webkul
 * @package     Webkul_MpSellerBuyerCommunication
 * @author      Webkul Software Private Limited
 *
 */
namespace Webkul\MpSellerBuyerCommunication\Block\Adminhtml\Query;

use Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication\CollectionFactory;

class ReplyButton extends \Magento\Backend\Block\Widget\Container
{

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Prepare button and grid
     *
     * @return \Webkul\MpSellerBuyerCommunication\Block\Adminhtml\Query\ReplyButton
     */
    protected function _prepareLayout()
    {
        $comm_id = $this->getRequest()->getParam('comm_id');
        $coll = $this->collectionFactory->create()
            ->addFieldToFilter('entity_id', $comm_id)
            ->addFieldToFilter('seller_id', 0);
        
        $BackButtonProps = [
            'id' => 'back',
            'label' => __('Back'),
            'class' => 'back',
            'name' => 'back',
            'onclick' => "setLocation('{$this->getBackUrl()}')"
        ];
        $this->buttonList->add('add_back', $BackButtonProps);
            $ReplyButtonProps = [
                'id' => 'add_reply',
                'label' => __('Reply'),
                'class' => 'primary',
                'name' => 'reply',
                'onclick' => "setLocation('{$this->getUrl('*/*/reply', array('comm_id' => $comm_id))}')"
            ];
            
            $this->buttonList->add('add_new', $ReplyButtonProps);
       
            return parent::_prepareLayout();
    }

    /**
     * Retrieve product create url
     *
     * @return string
     */
    protected function getBackUrl()
    {
        return $this->getUrl(
            '*/*/index'
        );
    }
}
