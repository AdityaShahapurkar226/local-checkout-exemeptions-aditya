<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMassUpload\Block\Adminhtml\Export\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Webkul\MpMassUpload\Helper\Data
     */
    protected $_massUploadHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Webkul\MpMassUpload\Helper\Data $massUploadHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Webkul\MpMassUpload\Helper\Data $massUploadHelper,
        array $data = []
    ) {
        $this->_massUploadHelper = $massUploadHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('run_form');
        $this->setTitle(__('Mass Upload'));
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $helper = $this->_massUploadHelper;
        $sellerList = $helper->getSellerList();
        $profiles = $helper->getProfiles();
        $form = $this->_formFactory->create(
            ['data' => [
                        'id' => 'edit_form',
                        'enctype' => 'multipart/form-data',
                        'action' => $this->getData('action'),
                        'method' => 'post']
                    ]
        );
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Mass Product Export'), 'class' => 'fieldset-wide']
        );
        $fieldset->addField(
            'seller_id',
            'select',
            [
                'label' => __('Select Seller'),
                'title' => __('Select Seller'),
                'name' => 'seller_id',
                'required' => true,
                'options' => $sellerList,
            ]
        );
        $fieldset->addField(
            'product_type',
            'select',
            [
                'label' => __('Select Product Type'),
                'title' => __('Select Product Type'),
                'name' => 'product_type',
                'required' => true,
                'options' => [
                    '' => __('Please select'),
                    'simple' => __('Export Simple Products'),
                    'configurable' => __('Export Configurable Products'),
                    'virtual' => __('Export Virtual Products'),
                    'downloadable' => __('Export Downloadable Products')
                ],
            ]
        );
        if ($helper->canSaveCustomAttribute()) {
            $fieldset->addField(
                'custom_attributes',
                'multiselect',
                [
                    'label' => __('Select Custom Attributes to be exported'),
                    'title' => __('Select Custom Attributes to be exported'),
                    'name' => 'custom_attributes',
                    'required' => false,
                    'values' => $this->toOptionArray(),
                    'disabled' => false
                ]
            );
        }
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Custom Attributes value array
     *
     * @return array
     */
    public function toOptionArray()
    {
        
        $attributeDetails=$this->_massUploadHelper->getAttributeDetails();
        $options=[];
        if (empty($attributeDetails)) {
            $options[]= ['label'=>__('No Attribute Enabled'),'value'=> ''];
            return $options;
        }
        foreach ($attributeDetails as $value => $label) {
            $options[]= ['label'=>__($label),'value'=>$label];
        }
        return $options;
    }
}
