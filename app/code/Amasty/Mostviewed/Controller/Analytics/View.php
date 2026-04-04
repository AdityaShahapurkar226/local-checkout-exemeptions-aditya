<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Controller\Analytics;

use Amasty\Mostviewed\Model\Analytics\View as ViewModel;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Session\SessionManagerInterface;
use Amasty\Mostviewed\Model\Analytics\ViewFactory;
use Amasty\Mostviewed\Api\ViewRepositoryInterface;
use Psr\Log\LoggerInterface;

class View implements HttpGetActionInterface
{
    public const PARAM_BLOCK_ID = 'block_id';

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;
    
    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var ViewFactory
     */
    private $viewFactory;

    /**
     * @var ViewRepositoryInterface
     */
    private $viewRepository;

    /**
     * @var array
     */
    private $visitorData;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ResultFactory $resultFactory,
        ViewFactory $viewFactory,
        ViewRepositoryInterface $viewRepository,
        SessionManagerInterface $sessionManager,
        LoggerInterface $logger,
        FormKeyValidator $formKeyValidator
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->resultFactory = $resultFactory;
        $this->viewFactory = $viewFactory;
        $this->viewRepository = $viewRepository;
        $this->visitorData = $sessionManager->getVisitorData();
        $this->logger = $logger;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->request->isAjax()) {
            $this->response->setStatusHeader(403, '1.1', 'Forbidden');
            throw new NotFoundException(__('Invalid Request'));
        }

        $data = ['error' => true];
        if ($this->formKeyValidator->validate($this->request)) {
            try {
                $this->updateCounter();
                $data = ['success' => true];
            } catch (\Exception $exception) {
                $this->logger->log(
                    \Monolog\Logger::ERROR,
                    'Cannot save mostviewed rules statistics. Error: ' . $exception->getMessage()
                );
            }
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($data);

        return $resultJson;
    }

    private function updateCounter(): void
    {
        $param = $this->request->getParam(self::PARAM_BLOCK_ID, false);
        if ($param) {
            /** @var ViewModel $viewModel */
            $viewModel = $this->viewFactory->create();
            $viewModel->setBlockId((int) $param);
            if (isset($this->visitorData['visitor_id'])) {
                $viewModel->setVisitorId((int) $this->visitorData['visitor_id']);
            }

            $this->viewRepository->save($viewModel);
        }
    }
}
