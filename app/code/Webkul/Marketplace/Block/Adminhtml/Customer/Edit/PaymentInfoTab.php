<?php

namespace Webkul\Marketplace\Block\Adminhtml\Customer\Edit;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Backend\Block\Template;
use Ahy\SavedCC\ViewModel\ConfigProvider;

class PaymentInfoTab extends Template implements TabInterface
{
    protected $_coreRegistry;
    protected $customerEdit;
    protected $configProvider;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Webkul\Marketplace\Block\Adminhtml\Customer\Edit $customerEdit,
        ConfigProvider $configProvider, 
        array $data = []
    ) {
        $this->_coreRegistry   = $registry;
        $this->customerEdit    = $customerEdit;
        $this->configProvider  = $configProvider;
        parent::__construct($context, $data);
    }

    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    public function getTabLabel()
    {
        return __('Payment Information');
    }

    public function getTabTitle()
    {
        return __('Payment Information');
    }

    public function canShowTab()
    {
        // 👇 Only show if customer exists AND SavedCC is enabled
        return (bool) $this->getCustomerId() && $this->isSavedCCEnabled();
    }

    public function isHidden()
    {
        // 👇 Hide if no customer OR SavedCC disabled
        return !$this->getCustomerId() || !$this->isSavedCCEnabled();
    }

    public function isSavedCCEnabled()
    {
        return $this->configProvider->isEnabled();
    }

    public function getTabClass()
    {
        return '';
    }

    public function getTabUrl()
    {
        return '';
    }

    public function isAjaxLoaded()
    {
        return false;
    }

    public function _toHtml()
    {
        $html = parent::_toHtml();
        $html .= $this->getLayout()->createBlock(
            \Webkul\Marketplace\Block\Adminhtml\Customer\Edit\Tab\PaymentInfo::class
        )->toHtml();

        return $html;
    }
}
