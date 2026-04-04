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
namespace Webkul\MpMassUpload\Block\Adminhtml\Upload\Edit;

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
        $this->setId('upload_form');
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
        $sets = $helper->getAttributeSets($flag = 1);
        $attributeProfiles = $helper->getAttributeProfiles();
        $isDownloadableAllowed = $helper->isProductTypeAllowed('downloadable');
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
            [
                'legend' => __('Profile Information'),
                'class' => 'fieldset-wide'
            ]
        );
        if ($isDownloadableAllowed) {
            $fieldset->addField(
                'is_downloadable',
                'checkbox',
                [
                    'name' => 'is_downloadable',
                    'label' => __('Downloadable'),
                    'title' => __('Downloadable')
                ]
            );
        }
        $fieldset->addField(
            'attribute_set',
            'select',
            [
                'label' => __('Attribute Set'),
                'title' => __('Attribute Set'),
                'name' => 'attribute_set',
                'required' => true,
                'options' => $sets,
            ]
        );
        $fieldset->addField(
            'attribute_profile_id',
            'select',
            [
                'label' => __('Dataflow Profile'),
                'title' => __('Dataflow Profile'),
                'name' => 'attribute_profile_id',
                'options' => $attributeProfiles,
            ]
        );
        $fieldset->addField(
            'massupload_csv',
            'file',
            [
                'name' => 'massupload_csv',
                'label' => __('Upload Csv/XML/XLS'),
                'title' => __('Upload Csv/XML/XLS'),
                'required' => true
            ]
        );
        $fieldset->addField(
            'massupload_image',
            'file',
            [
                'name' => 'massupload_image',
                'label' => __('Upload Images Zip'),
                'title' => __('Upload Images Zip'),
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
