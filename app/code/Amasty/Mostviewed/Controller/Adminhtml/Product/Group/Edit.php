<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Controller\Adminhtml\Product\Group;

use Amasty\Mostviewed\Model\Group;
use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_Mostviewed::rule';

    public const CURRENT_GROUP = 'amasty_mostviewed_product_group';

    /**
     * @var \Amasty\Mostviewed\Model\Repository\GroupRepository
     */
    private $groupRepository;

    /**
     * @var \Amasty\Mostviewed\Model\GroupFactory
     */
    private $groupFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    public function __construct(
        Action\Context $context,
        \Amasty\Mostviewed\Model\Repository\GroupRepository $groupRepository,
        \Amasty\Mostviewed\Model\GroupFactory $groupFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\Registry $coreRegistry
    ) {
        parent::__construct($context);
        $this->groupRepository = $groupRepository;
        $this->groupFactory = $groupFactory;
        $this->coreRegistry = $coreRegistry;
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @return Page|Redirect
     */
    public function execute()
    {
        $groupId = (int)$this->getRequest()->getParam('id');
        if ($groupId) {
            try {
                $model = $this->groupRepository->getById($groupId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This rule no longer exists.'));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath('*/*/index');
                return $resultRedirect;
            }
        } else {
            /** @var Group $model */
            $model = $this->groupFactory->create();
        }

        $this->applyFormName($model);

        // set entered data if was error when we do save
        $data = $this->dataPersistor->get(GROUP::PERSISTENT_NAME);
        if (!empty($data)) {
            $model->addData($data);
        }

        if ($model->getCategoryIds() !== null && !is_array($model->getCategoryIds())) {
            $model->setCategoryIds(explode(',', $model->getCategoryIds()));
        }

        $this->coreRegistry->register(self::CURRENT_GROUP, $model);

        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $resultPage->addBreadcrumb(__('Related Product Rules'), __('Related Product Rules'));

        // set title and breadcrumbs
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Related Product Rule'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getGroupId() ?
                __('Edit Related Product Rule # %1', $model->getGroupId())
                : __('New Related Product Rule')
        );

        $breadcrumb = $model->getGroupId() ?
            __('Edit Related Product Rule # %1', $model->getGroupId())
            : __('New Related Product Rule');
        $resultPage->addBreadcrumb($breadcrumb, $breadcrumb);

        return $resultPage;
    }

    /**
     * @param Group $model
     */
    private function applyFormName(Group &$model)
    {
        $model->getWhereConditions()->setFormName(Group::FORM_NAME);
        $model->getWhereConditions()->setJsFormObject(
            $model->getWhereConditionsFieldSetId(Group::FORM_NAME)
        );
        $model->getWhereConditions()->setRuleFactory($this->groupFactory);

        $model->getConditions()->setFormName(Group::FORM_NAME);
        $model->getConditions()->setJsFormObject(
            $model->getConditionsFieldSetId(Group::FORM_NAME)
        );
        $model->getConditions()->setRuleFactory($this->groupFactory);

        $model->getSameAsConditions()->setFormName(Group::FORM_NAME);
        $model->getSameAsConditions()->setJsFormObject(
            $model->getSameAsConditionsFieldSetId(Group::FORM_NAME)
        );
        $model->getSameAsConditions()->setRuleFactory($this->groupFactory);
    }
}
