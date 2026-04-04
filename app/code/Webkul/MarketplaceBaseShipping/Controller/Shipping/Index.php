<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MarketplaceBaseShipping
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceBaseShipping\Controller\Shipping;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\MarketplaceBaseShipping\Model\ShippingSettingRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;

class Index extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     *
     * @var ShippingSettingRepository
     */
    protected $shippingSettingRepository;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     */
    public function __construct(
        \Webkul\MarketplaceBaseShipping\Helper\Data $baseShippingHelper,
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        ShippingSettingRepository $shippingSettingRepository,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Framework\Registry $registry,
        StoreManagerInterface $storeManager
    ) {
        $this->baseShippingHelper = $baseShippingHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->_customerSession = $customerSession;
        $this->shippingSettingRepository = $shippingSettingRepository;
        $this->_coreRegistry = $registry;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get(\Magento\Customer\Model\Url::class)->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper=$this->baseShippingHelper->displayErrors();
        $helper->info('Base Shipping Logger');
        $isPartner = $this->marketplaceHelper->isSeller();
        if ($isPartner == 1) {
            $model = $this->shippingSettingRepository->getBySellerId($this->marketplaceHelper->getCustomerId());
            $this->_coreRegistry->register('shipping_setting', $model);
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->resultPageFactory->create();
            if ($this->marketplaceHelper->getIsSeparatePanel()) {
                $resultPage->addHandle('baseshipping_shipping_layout2_index');
            }
            $resultPage->getConfig()->getTitle()->set(__('Origin Address'));
            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/dashboard',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
