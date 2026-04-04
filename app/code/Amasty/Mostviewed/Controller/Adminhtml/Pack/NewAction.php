<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Controller\Adminhtml\Pack;

use Amasty\Mostviewed\Model\Pack;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\ResultFactory;

class NewAction extends Action
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

    public function __construct(DataPersistorInterface $dataPersistor, Context $context)
    {
        parent::__construct($context);
        $this->dataPersistor = $dataPersistor;
    }

    /**
     * @return Forward
     */
    public function execute()
    {
        $this->dataPersistor->clear(Pack::PERSISTENT_NAME);
        $forward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $forward->forward('edit');
        return $forward;
    }
}
