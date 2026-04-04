<?php

namespace Webkul\MarketplaceBaseShipping\Block;

class Marketplacehelper extends \Magento\Framework\View\Element\Template
{
    private $helperData;
    public function __construct(
        \Webkul\Marketplace\Helper\Data $helperData
    ) {
        $this->helperData= $helperData;
    }
    public function helperObj()
    {
        return $this->helperData ;
    }
}
