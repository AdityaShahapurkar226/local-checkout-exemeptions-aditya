<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Controller\Adminhtml\Product\Group;

use Amasty\Mostviewed\Model\GroupFactory;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Backend\App\Action;

class NewConditionHtml extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_Mostviewed::rule';

    /**
     * @var GroupFactory
     */
    private $groupFactory;

    public function __construct(
        Action\Context $context,
        GroupFactory $groupFactory
    ) {
        parent::__construct($context);
        $this->groupFactory = $groupFactory;
    }

    /**
     * Generate Condition HTML form. Ajax
     */
    public function execute()
    {
        $id = (string)$this->getRequest()->getParam('id');
        $form = $this->getRequest()->getParam('form');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        /* use object manager for factory like magento controller*/
        $model = $this->_objectManager->create($type)
            ->setId($id)
            ->setType($type)
            ->setRule($this->groupFactory->create())
            ->setPrefix('conditions');

        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof AbstractCondition) {
            if (strpos($form, 'where_conditions') !== false) {
                $model->setPrefix('where_conditions');
                $model->setWhereConditions([]);
            }
            if (strpos($form, 'same_as_conditions') !== false) {
                $model->setPrefix('same_as_conditions');
            }
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $model->setFormName($this->getRequest()->getParam('form_namespace'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }

        $this->getResponse()->setBody($html);
    }
}
