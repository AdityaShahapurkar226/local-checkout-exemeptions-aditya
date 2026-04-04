<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Controller\Adminhtml\Product\Group;

use Amasty\Mostviewed\Model\Group;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Amasty\Mostviewed\Model\OptionSource\BlockPosition;

class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_Mostviewed::rule';

    /**
     * @var \Amasty\Mostviewed\Model\Repository\GroupRepository
     */
    private $groupRepository;

    /**
     * @var \Amasty\Mostviewed\Model\GroupFactory
     */
    private $groupFactory;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Framework\DataObject
     */
    private $dataObject;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var BlockPosition
     */
    private $blockPosition;

    public function __construct(
        Action\Context $context,
        \Amasty\Mostviewed\Model\Repository\GroupRepository $groupRepository,
        \Amasty\Mostviewed\Model\GroupFactory $groupFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\DataObject $dataObject,
        BlockPosition $blockPosition,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->groupRepository = $groupRepository;
        $this->groupFactory = $groupFactory;
        $this->dataPersistor = $dataPersistor;
        $this->dataObject = $dataObject;
        $this->logger = $logger;
        $this->blockPosition = $blockPosition;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $data = $this->getRequest()->getPostValue();
        $groupId = (int)$this->getRequest()->getParam('group_id');
        if ($data) {
            /** @var Group $model */
            $model = $this->groupFactory->create();

            try {
                if ($groupId) {
                    $model = $this->groupRepository->getById($groupId);
                }
                $validateResult = $model->validateData($this->dataObject->addData($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addErrorMessage($errorMessage);
                    }
                    $this->dataPersistor->set(GROUP::PERSISTENT_NAME, $data);

                    $resultRedirect->setPath('amasty_mostviewed/*/edit', ['groupId' => $model->getRuleId()]);
                    return $resultRedirect;
                }

                $this->prepareData($data);
                $model->loadPost($data);
                $this->dataPersistor->set(GROUP::PERSISTENT_NAME, $data);
                $this->groupRepository->save($model);

                $this->messageManager->addSuccessMessage(__('The Rule was successfully saved'));
                $this->dataPersistor->clear(GROUP::PERSISTENT_NAME);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('amasty_mostviewed/*/edit', ['id' => $model->getGroupId()]);
                    return $resultRedirect;
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                if (empty($groupId)) {
                    $resultRedirect->setPath('amasty_mostviewed/*/newAction');
                    return $resultRedirect;
                } else {
                    $resultRedirect->setPath('amasty_mostviewed/*/edit', ['id' => $groupId]);
                    return $resultRedirect;
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the rule data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->dataPersistor->set(GROUP::PERSISTENT_NAME, $data);

                $resultRedirect->setPath('amasty_mostviewed/*/edit', ['id' => $groupId]);
                return $resultRedirect;
            }
        }
        $resultRedirect->setPath('amasty_mostviewed/*/');
        return $resultRedirect;
    }

    /**
     * @param array $data
     */
    private function prepareData(&$data)
    {
        if (isset($data['rule'])) {
            if (isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];
            }
            if (isset($data['rule']['same_as_conditions'])) {
                $data['same_as_conditions'] = $data['rule']['same_as_conditions'];
            }

            if (isset($data['rule']['where_conditions'])) {
                if ($this->blockPosition->getTypeByValue($data['block_position'])['value'] != 'category') {
                    $data['where_conditions'] = $data['rule']['where_conditions'];
                    $data['category_ids'] = '';
                } else {
                    $data['where_conditions'] = '';
                }
            }
            unset($data['rule']);
        }
    }
}
