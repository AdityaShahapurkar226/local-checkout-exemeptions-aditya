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
namespace Webkul\MpSellerBuyerCommunication\Plugin\Order;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\ObjectManagerInterface;

class View
{

    /**
     * Before Set Layout
     *
     * @param \Magento\Sales\Block\Adminhtml\Order\View $subject
     * @return void
     */
    public function beforeSetLayout(\Magento\Sales\Block\Adminhtml\Order\View $subject)
    {
        $subject->addButton(
            'sendordersms',
            [
                'label' => __('Contact'),
                'class' => 'askque'
            ]
        );

        return null;
    }
}
