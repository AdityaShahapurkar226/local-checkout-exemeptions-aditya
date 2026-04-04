<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AdminLoginAsVendor
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AdminLoginAsVendor\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Session\SessionManager;
use Magento\Security\Model\ResourceModel\AdminSessionInfo\CollectionFactory;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Psr\Log\LoggerInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Webkul\AdminLoginAsVendor\Helper\Data as Helper;
use Webkul\Marketplace\Model\ResourceModel\Seller\Grid\CollectionFactory as Sellerlist;

/**
 * Webkul AdminLoginAsVendor Account Login Controller.
 */
class Login extends \Magento\Framework\App\Action\Action
{
    
    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var SessionManager
     */
    protected $sessionManager;

    /**
     * @var CollectionFactory
     */
    public $sessionCollection;

    /**
     * @var RemoteAddress
     */
    public $remoteAddress;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var BackendHelper
     */
    private $backendHelper;

    /**
     * @var PhpCookieManager
     */
    private $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;
    
    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataManager;
    
    /**
     * @var \Webkul\AdminLoginAsVendor\Helper\Data
     */
    private $helper;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Seller\Grid\CollectionFactory
     */

    private $sellerlist;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */

    private $objectManager;

    /**
     * Constructor function
     *
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param SessionFactory $customerSession
     * @param CustomerFactory $customer
     * @param SessionManager $sessionManager
     * @param CollectionFactory $sessionCollection
     * @param RemoteAddress $remoteAddress
     * @param EncryptorInterface $encryptor
     * @param LoggerInterface $logger
     * @param JsonHelper $jsonHelper
     * @param BackendHelper $backendHelper
     * @param PhpCookieManager $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param Helper $helper
     * @param Sellerlist $sellerlist
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        SessionFactory $customerSession,
        CustomerFactory $customer,
        SessionManager $sessionManager,
        CollectionFactory $sessionCollection,
        RemoteAddress $remoteAddress,
        EncryptorInterface $encryptor,
        LoggerInterface $logger,
        JsonHelper $jsonHelper,
        BackendHelper $backendHelper,
        PhpCookieManager $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        Helper $helper,
        Sellerlist $sellerlist,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->customerSession = $customerSession;
        $this->customer = $customer;
        $this->sessionManager = $sessionManager;
        $this->sessionCollection = $sessionCollection;
        $this->remoteAddress = $remoteAddress;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
        $this->jsonHelper = $jsonHelper;
        $this->backendHelper = $backendHelper;
        $this->cookieMetadataManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->helper = $helper;
        $this->sellerlist = $sellerlist;
        $this->objectManager = $objectmanager;
        parent::__construct($context);
    }

    /**
     * AdminLoginAsVendor action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $this->getResponse()->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0', true);
        if ($this->helper->isEnable() && $cif = $this->getRequest()->getParam('cif')) {
            $cif = str_replace(" ", "+", $cif);
            try {
                $userInfoData = $this->jsonHelper->jsonDecode($this->encryptor->decrypt($cif));
                $admindetail = $userInfoData['admindata'];
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $objDate = $objectManager->create(\Magento\Framework\Stdlib\DateTime\DateTime::class);
                $date = $objDate->gmtDate();
                if ($this->isAdminloggedIn($userInfoData)) {
                    if (!empty($userInfoData['seller_id'])) {
                        $sellerId = $userInfoData['seller_id'];
                        $collection = $this->sellerlist->create();
                        $sellerData = $collection->addFieldToFilter('seller_id', $userInfoData['seller_id']);
                        foreach ($sellerData as $var) {
                            $sellerlog = $this->objectManager->
                            create(\Webkul\AdminLoginAsVendor\Model\Loginattempt::class);
                            $sellerlog->getCollection();
                            $sellerlog->setData('id', $var->getSellerId());
                            $sellerlog->setData('email', $var->getEmail());
                            $sellerlog->setData('name', $var->getName());
                            $sellerlog->setData('admin_id', $admindetail['user_id']);
                            $sellerlog->setData('admin_name', $admindetail['username']);
                            $sellerlog->setData('admin_email', $admindetail['email']);
                            $sellerlog->setData('website', $var->getShopUrl());
                            $sellerlog->setData('logged_in_at', $date);
                            $sellerlog->save();
                        }
                    } else {
                        $sellerId = $userInfoData['customer_id'];
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $customerFactory = $objectManager->get(\Magento\Customer\Model\CustomerFactory::class)->
                        create()->load($sellerId);
                        $customerlog = $this->objectManager->
                        create(\Webkul\AdminLoginAsVendor\Model\Loginattempt::class);
                        $customerlog->getCollection();
                        $customerlog->setData('id', $customerFactory->getEntityId());
                        $customerlog->setData('email', $customerFactory->getEmail());
                        $customerlog->setData('name', $customerFactory->
                        getFirstname().' '.$customerFactory->getLastname());
                        $customerlog->setData('admin_id', $admindetail['user_id']);
                        $customerlog->setData('admin_name', $admindetail['username']);
                        $customerlog->setData('admin_email', $admindetail['email']);
                        $customerlog->setData('website', 'Main Website');
                        $customerlog->setData('logged_in_at', $date);
                        $customerlog->save();
                    }
                    $loginFlag = 0;
                    // Check If any customer account already login
                    $customerSession = $this->customerSession->create();
                    if ($customerSession->isLoggedIn() && !(($customerSession->getCustomerId() != $sellerId))) {
                        $loginFlag = 1;
                    }
                    $this->checkLoginFlag($loginFlag, $sellerId);
                    if (!empty($userInfoData['seller_id'])) {
                        return $this->resultRedirectFactory->create()->setPath(
                            'marketplace/account/dashboard',
                            [
                                '_secure' => $this->getRequest()->isSecure()
                            ]
                        );
                    } else {
                        return $this->resultRedirectFactory->create()->setPath(
                            'customer/account/',
                            [
                                '_secure' => $this->getRequest()->isSecure()
                            ]
                        );
                    }
                } else {
                    $adminUrl = $this->backendHelper->getUrl('admin/auth/logout');
                    $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    $resultRedirect->setUrl($adminUrl);

                    return $resultRedirect;
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
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
     * @param array $userInfoData
     * @return boolean
     */
    protected function isAdminloggedIn($userInfoData)
    {
        if (!empty($userInfoData['asid'])) {
            $adminSessionId = '';
            $collection = $this->sessionCollection->create()
            ->addFieldToSelect(
                '*'
            )
            // ->addFieldToFilter('ip', $this->remoteAddress->getRemoteAddress())
            ->setOrder('id', 'DESC')
            ->setPageSize(1)
            ->setCurPage(1);
           
            foreach ($collection as $key => $value) {
                if ($value->getStatus()) {
                    $adminSessionId = $value->getUserId();
                }
            }
            if ($adminSessionId && ($adminSessionId == $userInfoData['asid'])) {
                return true;
            } else {
                $adminSessionId = '';
                $collection = $this->sessionCollection->create()
                ->addFieldToSelect(
                    '*'
                )
                // ->addFieldToFilter('ip', $this->remoteAddress->getRemoteAddress())
                ->setOrder('id', 'DESC')
                ->setPageSize(1)
                ->setCurPage(2);
                foreach ($collection as $key => $value) {
                    if ($value->getStatus()) {
                        $adminSessionId = $value->getUserId();
                    }
                }
                if ($adminSessionId && ($adminSessionId == $userInfoData['asid'])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * CookieMetaData
     *
     * @return void
     */
    protected function cookieMetaData()
    {
        if ($this->cookieMetadataManager->getCookie('mage-cache-sessid')) {
            $metadata = $this->cookieMetadataFactory->createCookieMetadata();
            $metadata->setPath('/');
            $this->cookieMetadataManager->deleteCookie('mage-cache-sessid', $metadata);
        }
    }

    /**
     * CheckLoginFlag function
     *
     * @param int $loginFlag
     * @param int $sellerId
     */
    protected function checkLoginFlag($loginFlag, $sellerId)
    {
        if (!$loginFlag) {
            if ($this->customer->create()->load($sellerId)->getId()) {
                $this->customerSession->create()->setId($sellerId);
                $this->customerSession->create()->loginById($sellerId);
                $customerSessionData = $this->sessionManager->getData();
                $visitorData = [];
                $visitorData['customer_id'] = $sellerId;
                $this->sessionManager->setVisitorData($visitorData);
                $this->cookieMetaData();
            } else {
                $resultForward = $this->resultForwardFactory->create();
                $resultForward->forward('noroute');
                return $resultForward;
            }
        }
    }
}
