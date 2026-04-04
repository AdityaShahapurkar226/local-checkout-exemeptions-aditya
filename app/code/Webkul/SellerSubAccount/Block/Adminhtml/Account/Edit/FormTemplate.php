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

class FormTemplate extends \Magento\Backend\Block\Template
{
    /**
     * Block template.
     *
     * @var string
     */
    public $_template = 'Webkul_SellerSubAccount::sub_account.phtml';

    /**
     * @var \Magento\Framework\Registry
     */
    public $registry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Return Seller ID
     *
     * @return int|null
     */
    public function getSellerId()
    {
        return $this->registry->registry('seller_id');
    }
}
