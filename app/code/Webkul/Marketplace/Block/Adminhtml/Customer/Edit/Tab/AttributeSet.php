<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Marketplace\Block\Adminhtml\Customer\Edit\Tab;

class AttributeSet extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    public const COMM_TEMPLATE = 'customer/attribute_set.phtml';

    /**
     * Construct
     *
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Webkul\Marketplace\Block\Adminhtml\Customer\Edit $customerEdit
     * @param \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSetOptions
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Backend\Block\Widget\Context $context,
        \Webkul\Marketplace\Block\Adminhtml\Customer\Edit $customerEdit,
        \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSetOptions,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->customerEdit = $customerEdit;
        $this->attributeSetOptions = $attributeSetOptions;
        parent::__construct($context, $data);
    }

    /**
     * Set template to itself.
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->canShowTab()) {
            $this->initForm();

            return parent::_toHtml();
        } else {
            return '';
        }
    }
    /**
     * Init form
     *
     * @return void
     */
    public function initForm()
    {
        if (!$this->canShowTab()) {
            return $this;
        }
        /**@var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('marketplace_');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Do You Want To Remove This Seller ?')]
        );

        $fieldset->addField(
            'is_seller_remove',
            'checkbox',
            [
                'name' => 'is_seller_remove',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Check To Unapprove Seller'),
                'title' => __('Check To Unapprove Seller'),
                'onchange' => 'this.value = this.checked;',
            ]
        );
        $this->setForm($form);

        return $this;
    }

    /**
     * Get payment info
     *
     * @return void
     */
    public function getPaymentInfo()
    {
        $paymentInfo = $this->customerEdit->getPaymentMode();
        return $paymentInfo;
    }
}
