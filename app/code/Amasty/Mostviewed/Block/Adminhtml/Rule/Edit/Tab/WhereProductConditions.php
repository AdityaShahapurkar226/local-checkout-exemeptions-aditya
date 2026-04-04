<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Block\Adminhtml\Rule\Edit\Tab;

use Amasty\Mostviewed\Controller\Adminhtml\Product\Group\Edit;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Form\Generic;

class WhereProductConditions extends Generic implements TabInterface
{
    /**
     * @var \Amasty\Mostviewed\Block\Form\Element\WhereConditions
     */
    protected $conditions;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        \Amasty\Mostviewed\Block\Form\Element\WhereConditions $conditions,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->conditions = $conditions;
    }

    /**
     * @return string
     */
    public function getNameInLayout()
    {
        return 'where_product_condition';
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Product Condition');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Product Condition');
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $formName = \Amasty\Mostviewed\Model\Group::FORM_NAME;
        /** @var \Amasty\Mostviewed\Model\Group $model */
        $model = $this->_coreRegistry->registry(Edit::CURRENT_GROUP);
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('group_where');

        /* start condition block*/
        $fieldset = $form->addFieldset(
            'conditions_fieldset',
            ['legend' => __('Conditions')]
        );
        $renderer = $this->getLayout()->createBlock(\Magento\Backend\Block\Widget\Form\Renderer\Fieldset::class);
        $renderer->setTemplate('Amasty_Mostviewed::rule/condition/fieldset.phtml')
            ->setComment(
                __('Choose the conditions to define the products the "related items" block will be displayed for.')
            )
            ->setFieldSetId($model->getWhereConditionsFieldSetId($formName))
            ->setNewChildUrl(
                $this->getUrl(
                    'amasty_mostviewed/product_group/newConditionHtml/form/'
                    . $model->getWhereConditionsFieldSetId($formName),
                    ['form_namespace' => $formName]
                )
            );

        $fieldset->setRenderer($renderer);

        $fieldset->addField(
            'where_conditions',
            'text',
            [
                'name'           => 'where_conditions',
                'label'          => __('Product Conditions'),
                'title'          => __('Product Conditions'),
                'required'       => true,
                'data-form-part' => $formName
            ]
        )
            ->setRule($model)
            ->setRenderer($this->conditions);

        $form->setValues($model->getData());
        $this->setConditionFormName(
            $model->getWhereConditions(),
            $formName,
            $model->getWhereConditionsFieldSetId($formName)
        );
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param \Magento\Rule\Model\Condition\AbstractCondition $conditions
     * @param string $formName
     *
     * @return void
     */
    private function setConditionFormName(
        \Magento\Rule\Model\Condition\AbstractCondition $conditions,
        $formName,
        $fieldsetName
    ) {
        $conditions->setFormName($formName);
        $conditions->setJsFormObject($fieldsetName);
        if ($conditions->getConditions() && is_array($conditions->getConditions())) {
            foreach ($conditions->getConditions() as $condition) {
                $this->setConditionFormName($condition, $formName, $fieldsetName);
            }
        }
    }
}
