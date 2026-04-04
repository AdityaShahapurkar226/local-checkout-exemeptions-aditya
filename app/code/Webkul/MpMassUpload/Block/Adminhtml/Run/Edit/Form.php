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
namespace Webkul\MpMassUpload\Block\Adminhtml\Run\Edit;

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
        $isMultiple = $this->getRequest()->getParam('multiple');
        
        $helper = $this->_massUploadHelper;
        $sellerList = $helper->getSellerList();
        $profiles = $helper->getProfiles();
        $stores = $helper->getAllStores();
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
            ['legend' => __('Run Profile'), 'class' => 'fieldset-wide']
        );
        $fieldset->addField(
            'store_to_upload',
            'select',
            [
                'name' => 'store_to_upload',
                'label' => __('Store'),
                'title' => __('Store to Upload'),
                'options' => $stores,
            ]
        );
        if (empty($isMultiple)) {
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
        }
       
        $fieldset->addField(
            'profile',
            'select',
            [
                'label' => __('Select Profile'),
                'title' => __('Select Profile'),
                'name' => 'profile',
                'required' => true,
                'options' => $profiles,
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
