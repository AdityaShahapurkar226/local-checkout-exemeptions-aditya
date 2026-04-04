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
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends Action
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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        Action\Context $context,
        \Amasty\Mostviewed\Model\Repository\GroupRepository $groupRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->groupRepository = $groupRepository;
        $this->logger = $logger;
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $groupId = (int)$this->getRequest()->getParam('id');
        if ($groupId) {
            try {
                $this->groupRepository->deleteById($groupId);
                $this->messageManager->addSuccessMessage(__('The rule have been deleted.'));

                $resultRedirect->setPath('amasty_mostviewed/*/');
                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Can\'t delete item right now. Please review the log and try again.')
                );
                $this->logger->critical($e);

                $resultRedirect->setPath('amasty_mostviewed/*/edit', ['id' => $groupId]);
                return $resultRedirect;
            }
        }

        $resultRedirect->setPath('amasty_mostviewed/*/');
        return $resultRedirect;
    }
}
