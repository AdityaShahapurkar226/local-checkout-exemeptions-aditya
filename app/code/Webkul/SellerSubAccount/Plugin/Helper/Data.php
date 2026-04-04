<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubAccount
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubAccount\Plugin\Helper;

use Webkul\SellerSubAccount\Helper\Data as HelperData;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\ObjectManagerInterface;

class Data
{
    /**
     * @var HelperData
     */
    public $_helper;

    /**
     * @var Request
     */
    public $request;

    /**
     * @var ObjectManagerInterface
     */
    public $_objectManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    public $moduleManager;

    /**
     * @var \Webkul\Marketplace\Model\Product
     */
    public $products;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlInterface;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirect;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @param HelperData $helper
     * @param Request $request
     * @param ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Webkul\Marketplace\Model\Product $products
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\Controller\Result\RedirectFactory $result
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\App\Response\Http $response
     */
    public function __construct(
        HelperData $helper,
        Request $request,
        ObjectManagerInterface $objectManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Webkul\Marketplace\Model\Product $products,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $result,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\Response\Http $response
    ) {
        $this->_helper = $helper;
        $this->_request = $request;
        $this->_objectManager = $objectManager;
        $this->moduleManager = $moduleManager;
        $this->products = $products;
        $this->_urlInterface = $urlInterface;
        $this->_messageManager = $messageManager;
        $this->resultRedirect = $result;
        $this->redirect = $redirect;
        $this->response = $response;
    }
  
    /**
     * Function to run to change the return seller id of current subaccount.
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param array $result
     *
     * @return bool
     */
    public function afterGetCustomerId(\Webkul\Marketplace\Helper\Data $helperData, $result)
    {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if ($subAccount->getId()) {
            return $this->_helper->getSubAccountSellerId();
        }
        return $result;
    }

    /**
     * Function to run to change the return data of afterIsSeller.
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param array $result
     *
     * @return bool
     */
    public function afterIsSellerGroupModuleInstalled(
        \Webkul\Marketplace\Helper\Data $helperData,
        $result
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if ($subAccount->getId()) {
            return true;
        }
        return $result;
    }

    /**
     * Function to run to change the return data of aroundIsAllowedAction.
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param \Closure $proceed
     * @param int $actionName
     *
     * @return bool
     */
    public function aroundIsAllowedAction(
        \Webkul\Marketplace\Helper\Data $helperData,
        \Closure $proceed,
        $actionName = ''
    ) {
        $sellerGroupHelper = "";
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            $result = $proceed($actionName);
            return $result;
        }
        $currentUrl =  $this->_urlInterface->getCurrentUrl();
        $allowedPermissionType = explode(',', $subAccount->getPermissionType());
        $adminPermissionList = $this->_helper->getSellerPermissionByCustomerId();
        $commonPermission = array_intersect($allowedPermissionType, $adminPermissionList);
        $dashbordSubString = strpos($currentUrl, 'marketplace/account/dashboard');
        if ($dashbordSubString == true) {
            if (!in_array('marketplace/account/dashboard', $commonPermission)) {
                $path = $commonPermission[0];
                $this->redirect->redirect($this->response, $path);
            }
        }
        
        $mappedControllers = $this->_helper->getAllPermissionTypes();
        $mappedControllers = array_change_key_case($mappedControllers, CASE_LOWER);
        $path = strtolower($actionName);
        $isPathAllowed = 0;

        if ($path !== 'marketplace/product/productlist') {
            if (strpos($path, 'marketplace/product/') !== false) {
                $path = 'marketplace/product/add';
            }
        }
    
        if (!empty($mappedControllers[$path]) && in_array($path, $commonPermission)) {
            $isPathAllowed = 1;
        }
       
        if ($this->moduleManager->isEnabled('Webkul_MpSellerGroup')) {
            $sellerGroupHelper = $this->_objectManager->create(\Webkul\MpSellerGroup\Helper\Data::class);
            if (!$sellerGroupHelper->getStatus() && $isPathAllowed) {
                return true;
            }
            $sellerId = $this->_helper->getSubAccountSellerId();
            $sellerGroupTypeRepository = $this->
                                        _objectManager->
                                        create(\Webkul\MpSellerGroup\Api\SellerGroupTypeRepositoryInterface::class);
            if (!$sellerGroupTypeRepository->getBySellerCount($sellerId)) {
                $this->products->getCollection()
                ->addFieldToFilter(
                    'seller_id',
                    $this->_helper->getSubAccountSellerId()
                );
                $getDefaultGroupStatus = $sellerGroupHelper->getDefaultGroupStatus();
                if ($getDefaultGroupStatus) {
                    $allowqty = $sellerGroupHelper->getDefaultProductAllowed();
                    $allowFunctionalities = explode(',', $sellerGroupHelper->getDefaultAllowedFeatures());
                    if ($allowqty >= count($this->products)) {
                        if (in_array($actionName, $allowFunctionalities, true) && $isPathAllowed) {
                            return true;
                        }
                    }
                }
            }
            $getSellerGroup = $sellerGroupTypeRepository->getBySellerId($sellerId);
            if (count($getSellerGroup->getData())) {
                $getSellerTypeGroup = $getSellerGroup;
                $allowedModuleArr = $helperData->getAllowedControllersBySetData(
                    $getSellerTypeGroup['allowed_modules_functionalities']
                );
                if (in_array($actionName, $allowedModuleArr, true) && $isPathAllowed) {
                    return true;
                }
            }
        }
        if ($isPathAllowed) {
            return true;
        }
        return false;
    }
}
