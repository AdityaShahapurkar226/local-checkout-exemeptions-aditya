<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Controller\Adminhtml\Pack;

use Amasty\Mostviewed\Model\Backend\Pack\Initialization as PackInitialization;
use Amasty\Mostviewed\Model\Pack;
use Amasty\Mostviewed\Model\Repository\PackRepository;
use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Save extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_Mostviewed::pack';

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PackRepository
     */
    private $packRepository;

    /**
     * @var PackInitialization
     */
    private $packInitialization;

    public function __construct(
        PackInitialization $packInitialization,
        Action\Context $context,
        PackRepository $packRepository,
        DataPersistorInterface $dataPersistor,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger;
        $this->packRepository = $packRepository;
        $this->packInitialization = $packInitialization;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $data = $this->getRequest()->getPostValue();
        $packId = (int) $this->getRequest()->getParam('pack_id');
        if ($data) {
            try {
                $model = $this->packInitialization->execute($packId, $data);
                $this->packRepository->save($model);

                $this->messageManager->addSuccessMessage(__('The Bundle Pack was successfully saved.'));
                $this->dataPersistor->clear(Pack::PERSISTENT_NAME);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('amasty_mostviewed/*/edit', ['id' => $model->getPackId()]);
                    return $resultRedirect;
                }
            } catch (LocalizedException $e) {
                $this->dataPersistor->set(Pack::PERSISTENT_NAME, $this->modifyDataForPersist($data));
                $this->messageManager->addErrorMessage($e->getMessage());
                if ($packId) {
                    $resultRedirect->setPath('amasty_mostviewed/*/edit', ['id' => $packId]);
                } else {
                    $resultRedirect->setPath('amasty_mostviewed/*/edit');
                }

                return $resultRedirect;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the pack data. Please review the error log.')
                );
                $this->logger->critical($e);
                $this->dataPersistor->set(Pack::PERSISTENT_NAME, $this->modifyDataForPersist($data));

                $resultRedirect->setPath('amasty_mostviewed/*/edit', ['id' => $packId]);
                return $resultRedirect;
            }
        }

        $resultRedirect->setPath('amasty_mostviewed/*/');
        return $resultRedirect;
    }

    private function modifyDataForPersist(array $data): array
    {
        $data['parent_ids'] = $data['parent_products_container'] ?? [];
        unset($data['parent_products_container']);

        return $data;
    }
}
