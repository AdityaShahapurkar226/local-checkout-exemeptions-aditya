<?php
namespace Webkul\MpSellerBuyerCommunication\Block\Adminhtml\Query\Reply;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class BackButton for showing back button on the grid.
 */
class BackButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get Button Data
     *
     * @return void
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getBackUrl()
    {
        $commId = $this->_request->getParam('comm_id');
        return $this->getUrl('*/*/view', ['comm_id' => $commId]);
    }
}
