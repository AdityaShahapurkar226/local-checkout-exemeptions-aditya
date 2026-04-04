<?php
namespace Ahy\EfflApiIntegration\Block\Adminhtml\Order\View;

class Info extends \Magento\Sales\Block\Adminhtml\Order\View\Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Ahy_EfflApiIntegration::order/view/info.phtml');
    }
}
