<?php
namespace Webkul\MpSellerBuyerCommunication\Block\Adminhtml\Query\Reply;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class SaveButton for showing save button on the grid.
 */
class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get Button Data
     *
     * @return void
     */
    public function getButtonData()
    {
        return [
            'label' => __('Send Message'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
        ];
    }
}
