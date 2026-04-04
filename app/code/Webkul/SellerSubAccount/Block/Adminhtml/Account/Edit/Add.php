<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Block\Adminhtml\Account\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class Add account add button
 */
class Add extends Generic implements ButtonProviderInterface
{

    /**
     * Get Button Data
     *
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($sellerId = $this->getSellerId()) {
            $data = [
                'label' => __('Add New Sub Account'),
                'class' => 'action- scalable primary',
                'on_click' => "location.href='".$this->getDeleteUrl($sellerId)."';",
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * Get Delete Url
     *
     * @param int $sellerId
     *
     * @return string
     */
    public function getDeleteUrl($sellerId)
    {
        return $this->getUrl('*/*/edit', ['seller_id' => $sellerId]);
    }
}
