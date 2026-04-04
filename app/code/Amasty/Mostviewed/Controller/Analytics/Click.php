<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Controller\Analytics;

use Amasty\Mostviewed\Api\ClickRepositoryInterface;
use Amasty\Mostviewed\Model\Analytics\Click as ClickModel;
use Amasty\Mostviewed\Model\Analytics\ClickFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Session\SessionManagerInterface;
use Psr\Log\LoggerInterface;

class Click implements HttpGetActionInterface
{
    public const PARAM_BLOCK_ID = 'block_id';
    public const PARAM_PRODUCT_ID = 'product_id';
    public const PARAM_CART_CLICK_ACTION = 'cart_click';

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
     * @var ClickFactory
     */
    private $clickFactory;

    /**
     * @var ClickRepositoryInterface
     */
    private $clickRepository;

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
        ClickFactory $clickFactory,
        ClickRepositoryInterface $clickRepository,
        SessionManagerInterface $sessionManager,
        LoggerInterface $logger,
        FormKeyValidator $formKeyValidator
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->resultFactory = $resultFactory;
        $this->clickFactory = $clickFactory;
        $this->clickRepository = $clickRepository;
        $this->visitorData = $sessionManager->getVisitorData();
        $this->logger = $logger;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
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
        $param = $this->request->getParam(self::PARAM_PRODUCT_ID, false);
        if ($param) {
            /** @var ClickModel $clickModel */
            $clickModel = $this->clickFactory->create();
            $clickModel->setProductId((int) $param);
            $clickModel->setBlockId((int) $this->request->getParam(self::PARAM_BLOCK_ID));
            $clickModel->setClickType((int) $this->request->getParam(self::PARAM_CART_CLICK_ACTION));
            if (isset($this->visitorData['visitor_id'])) {
                $clickModel->setVisitorId((int) $this->visitorData['visitor_id']);
            }

            $this->clickRepository->save($clickModel);
        }
    }
}
