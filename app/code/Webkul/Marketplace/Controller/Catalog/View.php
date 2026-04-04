<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Controller\Catalog;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Url\DecoderInterface;

class View extends \Magento\Catalog\Controller\Product\View
{
    /**
     * @var \Magento\Security\Model\AdminSessionInfo
     */
    protected $currentSession;

    /**
     * @param Context $context
     * @param \Magento\Catalog\Helper\Product\View $viewHelper
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param DecoderInterface $urlDecoder
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Helper\Product\View $viewHelper,
        \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        DecoderInterface $urlDecoder
    ) {
        parent::__construct($context, $viewHelper, $resultForwardFactory, $resultPageFactory);
        $this->urlDecoder = $urlDecoder;
    }

    /**
     * Product view action
     *
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if ($this->isAdminloggedIn()) {
            // Get initial data from request
            $categoryId = (int) $this->getRequest()->getParam('category', false);
            $productId = (int) $this->getRequest()->getParam('id');
            $specifyOptions = $this->getRequest()->getParam('options');

            // Prepare helper and params
            $params = new \Magento\Framework\DataObject();
            $params->setCategoryId($categoryId);
            $params->setSpecifyOptions($specifyOptions);

            // Render page
            try {
                $page = $this->resultPageFactory->create();
                $this->viewHelper = $this->_objectManager->create(\Webkul\Marketplace\Helper\Product\View::class);
                $this->viewHelper->prepareAndRender($page, $productId, $this, $params);
                return $page;
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return $this->noProductRedirect();
            } catch (\Exception $e) {
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $resultForward = $this->resultForwardFactory->create();
                $resultForward->forward('noroute');
                return $resultForward;
            }
        } else {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('noroute');
            return $resultForward;
        }
    }
    /**
     * Check if admin logged in or not
     *
     * @return boolean
     */
    public function isAdminloggedIn()
    {
        $sessionId = $this->getRequest()->getParam('SID');
        $dateTime = $this->_objectManager->create(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        $adminConfig = $this->_objectManager->create(\Magento\Security\Model\Config::class);
        $lifetime = $adminConfig->getAdminSessionLifetime();
        $currentTime = $dateTime->gmtTimestamp();
        $currentSession = $this->getCurrentSession($this->urlDecoder->decode($sessionId));
        $lastUpdatedTime = $dateTime->gmtTimestamp($currentSession->getUpdatedAt());
        if (!is_numeric($lastUpdatedTime)) {
            $lastUpdatedTime = strtotime($lastUpdatedTime);
        }
        if ($lastUpdatedTime >= ($currentTime - $lifetime) &&
            $currentSession->getStatus() == 1
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * GetCurrentSession
     *
     * @param  string $sessionId [admin session id]
     * @return AdminSessionInfo
     */
    protected function getCurrentSession($sessionId)
    {
        if (!$this->currentSession) {
            $this->currentSession = $this->_objectManager->create(
                \Magento\Security\Model\AdminSessionInfoFactory::class
            )->create();
            $this->currentSession->load($sessionId, 'id');
        }
        return $this->currentSession;
    }
}
