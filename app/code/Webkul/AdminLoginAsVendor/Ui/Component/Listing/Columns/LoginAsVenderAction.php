<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AdminLoginAsVendor
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AdminLoginAsVendor\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Security\Model\AdminSessionsManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Webkul\AdminLoginAsVendor\Helper\Data as Helper;
use Webkul\Marketplace\Helper\Data as MpHelper;

/**
 * Class AdminLoginAsVendor Actions.
 */
class LoginAsVenderAction extends Column
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var AdminSessionsManager
     */
    protected $adminSessionsManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * Constructor function
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param AdminSessionsManager $adminSessionsManager
     * @param StoreManagerInterface $storeManager
     * @param EncryptorInterface $encryptor
     * @param JsonHelper $jsonHelper
     * @param Helper $helper
     * @param MpHelper $mpHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        AdminSessionsManager $adminSessionsManager,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        JsonHelper $jsonHelper,
        Helper $helper,
        MpHelper $mpHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        array $components = [],
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->adminSessionsManager = $adminSessionsManager;
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->mpHelper = $mpHelper;
        $this->customerFactory = $customerFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare function
     */
    public function prepare()
    {
        if ($this->helper->isEnable()) {
            $this->_data['config']['componentDisabled'] = false;
        } else {
            $this->_data['config']['componentDisabled'] = true;
        }
        parent::prepare();
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $customer = $this->customerFactory->create();
                $customerModel = $customer->load($item['customer_id']);
                $isVendorFlag = 0;
                 $isseller = $this->mpHelper->getSellerCollectionObj($item['customer_id']);
                
                $websiteId = $customerModel->getWebsiteId();
                $loginUrls = '';
                $allUrls = $this->getAllWebsiteBaseUrls($websiteId);
                $adminSessionId = $this->adminSessionsManager
                ->getCurrentSession()
                ->getUserId();
                
                $urlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'seller_id';
                if ($urlEntityParamName == 'seller_id') {
                    if (count($isseller) > 0) {
                        $isVendorFlag = 1;
                    } else {
                        $isVendorFlag = 0;
                    }
                    $userId = $item['customer_id'];
                } else {
                    $userId = $item['entity_id'];
                }
                $userInfo = [$urlEntityParamName => $userId, 'asid' => $adminSessionId];
                $userInfo = $this->encryptor->encrypt($this->jsonHelper->jsonEncode($userInfo));
                if (!empty($allUrls)) {
                    foreach ($allUrls as $url => $value) {
                        $actionUrl = $url.'adminloginasvendor/account/login/?cif='.$userInfo;
                        if ($isVendorFlag) {
                            $loginUrls = $loginUrls.
                            "<a 
                                href='".$actionUrl."' 
                                target='_blank' 
                                title='".__('Login As Vendor')."'
                            >".__('Login As Vendor for %1', $value)."</a><br/>";
                        } else {
                            $loginUrls = $loginUrls.
                            "<a 
                                href='".$actionUrl."' 
                                target='_blank' 
                                title='".__('Login As Customer')."'
                            >".__('Login As Customer for %1', $value)."</a><br/>";
                        }
                    }
                } else {
                    $url = $this->storeManager->getStore()->getBaseUrl();
                    $actionUrl = $url.'adminloginasvendor/account/loginasvender/?cif='.$userInfo;
                    if ($isVendorFlag) {
                        $loginUrls = $loginUrls.
                        "<a 
                            href='".$actionUrl."' 
                            target='_blank' 
                            title='".__('Login As Vendor')."'
                        >".__('Login As Vendor')."</a>";
                    } else {
                        $loginUrls = $loginUrls.
                        "<a 
                            href='".$actionUrl."' 
                            target='_blank' 
                            title='".__('Login As Customer')."'
                        >".__('Login As Customer')."</a>";
                    }
                }
                $item[$this->getData('name')] = $loginUrls;
            }
        }
        return $dataSource;
    }

    /**
     * Get website url by product id
     *
     * @param  int $websiteId
     * @return string
     */
    public function getAllWebsiteBaseUrls($websiteId)
    {
        $customerScope=$this->scopeConfig->getValue('customer/account_share/scope');
        $allUrls = [];
        $websites = $this->storeManager->getWebsites();
        if (count($websites) > 1) {
            foreach ($websites as $website) {
                if ($customerScope==1) {
                    if ($website->getWebsiteId()==$websiteId) {
                        foreach ($website->getStores() as $store) {
                            $storeObj = $this->storeManager->getStore($store);
                            $allUrls[$storeObj->getBaseUrl()] = $website->getName();
                        }
                    }
                } else {
                    foreach ($website->getStores() as $store) {
                        $storeObj = $this->storeManager->getStore($store);
                        $allUrls[$storeObj->getBaseUrl()] = $website->getName();
                    }
                }
            }
        }
        return $allUrls;
    }
}
