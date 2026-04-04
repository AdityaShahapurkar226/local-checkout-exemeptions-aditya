<?php

namespace Webkul\MarketplaceBaseShipping\Block;

class Dimension extends \Magento\Framework\View\Element\Template
{
    private $helperData;
    public function __construct(
        \Webkul\MarketplaceBaseShipping\Helper\Data $helperData
    ) {
        $this->helperData= $helperData;
    }
    public function getDimensionsUnit()
    {
        return $this->helperData->getDimensionsUnit();
    }
}
