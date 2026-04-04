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
namespace Webkul\SellerSubAccount\Block;

class SubAccount extends \Magento\Framework\View\Element\Template
{
    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\SellerSubAccount\Helper\Data $subAccHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\SellerSubAccount\Helper\Data $subAccHelper,
        array $data = []
    ) {
        $this->_mpHelper = $mpHelper;
        $this->_subAccHelper = $subAccHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get Marketplace Helper Instance
     *
     * @return object
     */
    public function getMarketplaceHelperInstance()
    {
        return $this->_mpHelper;
    }

    /**
     * Get Sub Account Helper
     *
     * @return object
     */
    public function getSubAccountHelper()
    {
        return $this->_subAccHelper;
    }
}
