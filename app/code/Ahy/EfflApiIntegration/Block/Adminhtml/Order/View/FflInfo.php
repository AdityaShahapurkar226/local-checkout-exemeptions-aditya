<?php

namespace Ahy\EfflApiIntegration\Block\Adminhtml\Order\View;

use Magento\Sales\Block\Adminhtml\Order\View\Info as OrderViewInfo;

class FflInfo extends OrderViewInfo
{
    // Inherits all needed functions; just uses your template
    protected $_template = 'Ahy_EfflApiIntegration::order/view/ffl-info.phtml';
}
