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


namespace Webkul\MpSellerBuyerCommunication\Block\Adminhtml;

use Webkul\MpSellerBuyerCommunication\Model\Source\QueryStatus;
use Webkul\MpSellerBuyerCommunication\Model\ResourceModel\SellerBuyerCommunication\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class Seller extends \Magento\Framework\View\Element\Template
{

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->order = $orderRepository;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $data);
    }
    /**
     * Get Order Info
     *
     * @param int $orderId
     * @return void
     */
    public function getOrderInfo($orderId)
    {
        $collection = $this->order->get($orderId);
        return $collection;
    }
    /**
     * Get Wysiwyg Config
     *
     * @return void
     */
    public function getWysiwygConfig()
    {
        $config = $this->_wysiwygConfig->getConfig();
        // $config = json_encode($config->getData());
        return $config;
    }
}
