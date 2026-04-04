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
namespace Webkul\SellerSubAccount\Block\Adminhtml\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;

/**
 * Class BackButton block back button
 */
class BackButton extends Generic implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    public $context;

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    public $request;

    /**
     * @param Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->context = $context;
        $this->registry = $registry;
        $this->request = $request;
    }

    /**
     * Get Button Data
     *
     * @return array
     */
    public function getButtonData()
    {
        $button = [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getUrlBack()),
            'class' => 'back',
            'sort_order' => 10
        ];
        return $button;
    }

    /**
     * Get URL for back (reset) button
     *
     * @return string
     */
    public function getUrlBack()
    {
        return $this->getUrl('sellersubaccount/account/manage', ['seller_id'=>$this->request->getParam('seller_id')]);
    }
}
