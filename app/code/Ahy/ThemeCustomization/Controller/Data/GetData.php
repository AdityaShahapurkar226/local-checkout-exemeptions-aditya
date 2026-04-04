<?php

namespace Ahy\ThemeCustomization\Controller\Data;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;

use Psr\Log\LoggerInterface;

class GetData extends Action
{
    protected $deploymentConfig;
    protected $pageFactory;
    protected $_logger;

    public function __construct(
        Context $context,
        DeploymentConfig $deploymentConfig,
        PageFactory $pageFactory,
        LoggerInterface $logger
    ) {
        $this->deploymentConfig = $deploymentConfig;
        $this->pageFactory = $pageFactory;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $dataConfig = $this->deploymentConfig->get('db');
            echo '<pre>' . htmlspecialchars(print_r($dataConfig, true)) . '</pre>';
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
