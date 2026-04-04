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

/**
 * Conversation block
 *
 * @author      Webkul Software
 */
class Contact extends \Magento\Framework\View\Element\Template
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Marketplace\Model\OrdersFactory $mpOrderModel
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Webkul\MpSellerBuyerCommunication\Helper\Data $helper
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Cms\Helper\Wysiwyg\Images|null $wysiwygImages
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Model\OrdersFactory $mpOrderModel,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Webkul\MpSellerBuyerCommunication\Helper\Data $helper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Cms\Helper\Wysiwyg\Images $wysiwygImages = null,
        array $data = []
    ) {
        $this->mpOrderModel = $mpOrderModel;
        $this->customer = $customerRepositoryInterface;
        $this->helper = $helper;
        $this->order = $orderRepository;
        $this->wysiwygImages = $wysiwygImages ?: \Magento\Framework\App\ObjectManager::getInstance()
                                  ->create(\Magento\Cms\Helper\Wysiwyg\Images::class);
        parent::__construct($context, $data);
    }

    /**
     * Get Seller Order Info
     *
     * @param string $orderId
     * @return void
     */
    public function getSellerOrderInfo($orderId = '')
    {
        $sellerIds = [];
        $collection = $this->mpOrderModel->create()->getCollection()
        ->addFieldToFilter(
            'order_id',
            ['eq' => $orderId]
        );
        foreach ($collection as $data) {
            if ($data['seller_id'] !=0) {
                $sellerDetails = $this->customer->getById($data['seller_id']);
                $sellerIds[$data['seller_id']] = $sellerDetails->getFirstname().' '.
                $sellerDetails->getLastname();
            } else {
                $sellerIds[0] = 'Administrator';
            }
           
        }
        
        return $sellerIds;
    }

    /**
     * Get Admin Order Status
     *
     * @return void
     */
    public function getAdminOrderStatus()
    {
        return $this->helper->getAdminOrderStatus();
    }

    /**
     * Get Order Status
     *
     * @param int $orderId
     * @return void
     */
    public function getOrderStatus($orderId)
    {
        $order = $this->order->get($orderId);
        $state = $order->getStatus();
        return $state;
    }
    /**
     * Get wysiwyg url
     *
     * @return string
     */
    public function getWysiwygUrl()
    {
        $currentTreePath = $this->wysiwygImages->idEncode(
            \Magento\Cms\Model\Wysiwyg\Config::IMAGE_DIRECTORY
        );
        $url =  $this->getUrl(
            'marketplace/wysiwyg_images/index',
            [
                'current_tree_path' => $currentTreePath
            ]
        );
        return $url;
    }
}
