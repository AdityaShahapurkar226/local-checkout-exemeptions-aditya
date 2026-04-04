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

namespace Webkul\Marketplace\Block\Adminhtml\Customer\Edit;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;

/**
 * Customer account form block.
 */
class AttributeSetTab extends Generic implements TabInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Construct
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Webkul\Marketplace\Block\Adminhtml\Customer\Edit $customerEdit
     * @param \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSetOptions
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Webkul\Marketplace\Block\Adminhtml\Customer\Edit $customerEdit,
        \Magento\Catalog\Model\Product\AttributeSet\Options $attributeSetOptions,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->customerEdit = $customerEdit;
        $this->attributeSetOptions = $attributeSetOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Get customer id
     *
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Get tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Assign Attribute Set');
    }

    /**
     * Get tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Assign Attribute Set');
    }

    /**
     * Can show tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        $coll = $this->customerEdit->getMarketplaceUserCollection();
        $isSeller = false;
        foreach ($coll as $row) {
            $isSeller = $row->getIsSeller();
        }
        if ($this->getCustomerId() && $isSeller) {
            return true;
        }

        return false;
    }

    /**
     * Get is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        $coll = $this->customerEdit->getMarketplaceUserCollection();
        $isSeller = false;
        foreach ($coll as $row) {
            $isSeller = $row->getIsSeller();
        }
        if ($this->getCustomerId() && $isSeller) {
            return false;
        }

        return true;
    }

    /**
     * Get Seller AttributeSet id
     *
     * @return array
     */
    public function getAttributeSetId()
    {
        $coll = $this->customerEdit->getMarketplaceUserCollection();
        $attributeSetid = [];
        foreach ($coll as $row) {
            $currentSetId = $row->getAllowedAttributesetIds() ?? '';
            $attributeSetid = json_decode($currentSetId);
        }

        return $attributeSetid;
    }

    /**
     * Tab class getter.
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content.
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call.
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * To html
     *
     * @return string
     */
    protected function _toHtml()
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
            ['legend' => __('Assign Attribute Set to Vendor')]
        );
        $currentAttributeSetId = $this->getAttributeSetId();

        $fieldset->addField(
            'attribute_set',
            'multiselect',
            [
                'name' => 'attribute_set',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Attribute Set'),
                'title' => __('Attribute Set'),
                'value' => $currentAttributeSetId,
                'values' => $this->attributeSetOptions->toOptionArray(),
            ]
        );
        $this->setForm($form);

        return $this;
    }
}
