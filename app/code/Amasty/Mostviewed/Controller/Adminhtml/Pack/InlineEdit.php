<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Controller\Adminhtml\Pack;

use Amasty\Mostviewed\Model\Pack;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class InlineEdit extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_Mostviewed::pack';

    /**
     * @var \Amasty\Mostviewed\Model\Repository\PackRepository
     */
    private $packRepository;

    public function __construct(
        Action\Context $context,
        \Amasty\Mostviewed\Model\Repository\PackRepository $packRepository
    ) {
        parent::__construct($context);
        $this->packRepository = $packRepository;
    }

    /**
     * Inline edit action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $error = false;
        $messages = [];

        $postItems = $this->getRequest()->getParam('items', []);
        if ($this->getRequest()->getParam('isAjax') && count($postItems)) {
            foreach ($postItems as $itemId => $itemData) {
                /** @var Pack $model */
                $model = $this->packRepository->getById($itemId);
                try {
                    $model->addData($itemData);
                    $this->packRepository->save($model);
                } catch (LocalizedException $e) {
                    $messages[] = $e->getMessage();
                    $error = true;
                } catch (\Exception $e) {
                    $messages[] = __('Something went wrong while saving the item.');
                    $error = true;
                }
            }
        } else {
            $messages[] = __('Please correct the data sent.');
            $error = true;
        }

        return $resultJson->setData(
            [
                'messages' => $messages,
                'error'    => $error
            ]
        );
    }
}
