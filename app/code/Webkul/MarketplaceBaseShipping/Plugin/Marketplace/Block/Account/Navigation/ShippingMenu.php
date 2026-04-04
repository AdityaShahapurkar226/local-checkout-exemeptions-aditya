<?php

namespace Webkul\MarketplaceBaseShipping\Plugin\Marketplace\Block\Account\Navigation;

class ShippingMenu
{

    protected $scopeConfig;
    protected $shipconfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Config $shipconfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->shipconfig = $shipconfig;
    }

    public function afterIsShippineAvlForSeller(
        \Webkul\Marketplace\Block\Account\Navigation\ShippingMenu $subject,
        $status
    ) {
        if (!($status)) {
            $activeCarriers = $this->shipconfig->getActiveCarriers();
            foreach ($activeCarriers as $carrierCode => $carrierModel) {
                $active = $this->scopeConfig->getValue(
                    'carriers/'.$carrierCode.'/active'
                );
                if ($active) {
                    $status = true;
                }
            }
        }
        return $status;
    }
}
