<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Controller\Adminhtml\Pack;

use Amasty\Mostviewed\Model\Pack;
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
    public const ADMIN_RESOURCE = 'Amasty_Mostviewed::pack';

    /**
     * @var \Amasty\Mostviewed\Model\Backend\Pack\Registry
     */
    private $packRegistry;

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Amasty\Mostviewed\Api\PackRepositoryInterface
     */
    private $packRepository;

    /**
     * @var \Amasty\Mostviewed\Model\PackFactory
     */
    private $packFactory;

    public function __construct(
        Action\Context $context,
        \Amasty\Mostviewed\Api\PackRepositoryInterface $packRepository,
        \Amasty\Mostviewed\Model\PackFactory $packFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Amasty\Mostviewed\Model\Backend\Pack\Registry $packRegistry
    ) {
        parent::__construct($context);
        $this->packRegistry = $packRegistry;
        $this->dataPersistor = $dataPersistor;
        $this->packRepository = $packRepository;
        $this->packFactory = $packFactory;
    }

    /**
     * @return Redirect|Page
     */
    public function execute()
    {
        $packId = (int)$this->getRequest()->getParam('id');
        if ($packId) {
            try {
                $model = $this->packRepository->getById($packId, true);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage(__('This Bundle Pack no longer exists.'));

                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setPath('*/*/index');
                return $resultRedirect;
            }
        } else {
            /** @var Pack $model */
            $model = $this->packFactory->create();
        }

        // set entered data if was error when we do save
        $data = $this->dataPersistor->get(Pack::PERSISTENT_NAME);
        if (!empty($data) && !$model->getPackId()) {
            $model->addData($data);
        }

        $this->packRegistry->set($model);

        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE);
        $resultPage->addBreadcrumb(__('Bundle Packs'), __('Bundle Packs'));

        // set title and breadcrumbs
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Bundle Pack'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getPackId() ?
                __('Edit Bundle Pack # %1', $model->getPackId())
                : __('New Bundle Pack')
        );

        $breadcrumb = $model->getPackId() ?
            __('Edit Bundle Pack # %1', $model->getPackId())
            : __('New Bundle Pack');
        $resultPage->addBreadcrumb($breadcrumb, $breadcrumb);

        return $resultPage;
    }
}
