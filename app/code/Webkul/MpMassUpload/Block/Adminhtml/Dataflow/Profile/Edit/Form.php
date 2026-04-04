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
namespace Webkul\MpMassUpload\Block\Adminhtml\Dataflow\Profile\Edit;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Session\SessionManagerInterface;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Webkul\MpMassUpload\Helper\Data
     */
    protected $_massUploadHelper;

    /**
     * @var SessionManagerInterface
     */
    protected $session;

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
        $this->setId('dataflow_profile_form');
        $this->setTitle(__('Mass Upload Dataflow Profile'));
    }

    /**
     * Get session object.
     *
     * @return SessionManagerInterface
     */
    protected function getSession()
    {
        if ($this->session === null) {
            $this->session = ObjectManager::getInstance()->get(
                \Magento\Framework\Session\SessionManagerInterface::class
            );
        }

        return $this->session;
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $helper = $this->_massUploadHelper;
        $sets = $helper->getAttributeSets();
        $attributeProfiles = $helper->getAttributeProfiles();
        $data = $this->getSession()->getAttributeProfileFormData();
        if (!empty($data)) {
            $id = isset($data['mpmassupload_dataflow_profile']['entity_id'])
            ? $data['mpmassupload_dataflow_profile']['entity_id'] : null;
            $attributeProfileId = isset($data['mpmassupload_dataflow_profile']['entity_id'])
            ? $data['mpmassupload_dataflow_profile']['entity_id'] : null;
            $profileName = isset($data['mpmassupload_dataflow_profile']['profile_name'])
            ? $data['mpmassupload_dataflow_profile']['profile_name'] : null;
            $attributeSetId = isset($data['mpmassupload_dataflow_profile']['attribute_set_id'])
            ? $data['mpmassupload_dataflow_profile']['attribute_set_id'] : null;
        } else {
            $id = null;
            $attributeProfileId = null;
            $profileName = null;
            $attributeSetId = null;
        }
        $this->getSession()->unsAttributeProfileFormData();
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
            ['legend' => __('Dataflow Profile Information'), 'class' => 'fieldset-wide']
        );
        $fieldset->addField(
            'id',
            'hidden',
            [
                'name' => 'id',
                'value' => $id
            ]
        );
        $fieldset->addField(
            'profile_name',
            'text',
            [
                'label' => __('Profile Name'),
                'title' => __('Profile Name'),
                'name' => 'profile_name',
                'required' => true,
                'options' => $sets,
                'value' => $profileName
            ]
        );
        $fieldset->addField(
            'attribute_set_id',
            'select',
            [
                'label' => __('Attribute Set'),
                'title' => __('Attribute Set'),
                'name' => 'attribute_set_id',
                'required' => true,
                'options' => $sets,
                'value' => $attributeSetId
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
