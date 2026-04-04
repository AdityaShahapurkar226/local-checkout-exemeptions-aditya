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
namespace Webkul\SellerSubAccount\Helper;

use Magento\Framework\App\Helper\Context;
use Webkul\SellerSubAccount\Api\SubAccountRepositoryInterface;
use Webkul\SellerSubAccount\Model\SubAccount;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Customer\Mapper as CustomerMapper;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Customer\Model\ResourceModel\Group\Collection as GroupCollection;
use Webkul\Marketplace\Model\ControllersRepository;
use Magento\Framework\View\Result\PageFactory as ResultPageFactory;
use Magento\Customer\Model\Session;

/**
 * Webkul SellerSubAccount Helper Data.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var SubAccountRepositoryInterface
     */
    public $_subAccountRepository;

    /**
     * @var SubAccount
     */
    public $subAccount;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $_customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    public $_customerRepository;

    /**
     * @var CustomerMapper
     */
    public $_customerMapper;

    /**
     * @var CustomerInterfaceFactory
     */
    public $_customerFactory;

    /**
     * @var AccountManagementInterface
     */
    public $_accountManagement;

    /**
     * @var DataObjectHelper
     */
    public $_dataObjectHelper;

    /**
     * @var GroupCollection
     */
    public $_groupCollection;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    public $helperData;

    /**
     * @var ControllersRepository
     */
    public $_controllersRepository;

    /**
     * @var ResultPageFactory
     */
    public $resultPageFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param SubAccountRepositoryInterface $subAccountRepository
     * @param SubAccount $subAccount
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerMapper $customerMapper
     * @param CustomerInterfaceFactory $customerFactory
     * @param AccountManagementInterface $accountManagement
     * @param DataObjectHelper $dataObjectHelper
     * @param GroupCollection $groupCollection
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param FormFactory $formFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param AddressInterfaceFactory $addressDataFactory
     * @param ControllersRepository $controllersRepository
     * @param \Webkul\Marketplace\Model\SellerFactory $sellerModel
     * @param \Webkul\SellerSubAccount\Model\SubAccountFactory $subAccountSeller
     * @param \Magento\Customer\Model\CustomerFactory $customerModFactory
     * @param \Magento\Framework\Registry $registry
     * @param ResultPageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        SubAccountRepositoryInterface $subAccountRepository,
        SubAccount $subAccount,
        Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        CustomerMapper $customerMapper,
        CustomerInterfaceFactory $customerFactory,
        AccountManagementInterface $accountManagement,
        DataObjectHelper $dataObjectHelper,
        GroupCollection $groupCollection,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Webkul\Marketplace\Helper\Data $helperData,
        FormFactory $formFactory,
        RegionInterfaceFactory $regionDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        ControllersRepository $controllersRepository,
        \Webkul\Marketplace\Model\SellerFactory $sellerModel,
        \Webkul\SellerSubAccount\Model\SubAccountFactory $subAccountSeller,
        \Magento\Customer\Model\CustomerFactory $customerModFactory,
        \Magento\Framework\Registry $registry,
        ResultPageFactory $resultPageFactory
    ) {
        $this->_subAccountRepository = $subAccountRepository;
        $this->subAccount = $subAccount;
        $this->_customerSession = $customerSession;
        $this->_customerRepository = $customerRepository;
        $this->_customerMapper = $customerMapper;
        $this->_customerFactory = $customerFactory;
        $this->customerModFactory=$customerModFactory;
        $this->formFactory = $formFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->_accountManagement = $accountManagement;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->registry = $registry;
        $this->_groupCollection = $groupCollection;
        $this->_moduleList = $moduleList;
        $this->helperData = $helperData;
        $this->_controllersRepository = $controllersRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->_sellerModel = $sellerModel;
        $this->subAccountSeller = $subAccountSeller;
        parent::__construct($context);
    }

    /**
     * Get Account Group
     *
     * @return void
     */
    public function getAccountGroup()
    {
        $groupId = 1;
        $coll = $this->_groupCollection
            ->addFieldToFilter('customer_group_code', 'Sub Account');
        foreach ($coll as $key => $value) {
            if ($value->getCustomerGroupCode() == 'Sub Account') {
                $groupId = $value->getCustomerGroupId();
            }
        }
        return $groupId;
    }

    /**
     * Get Account General Group
     *
     * @return void
     */
    public function getAccountGeneralGroup()
    {
        $groupId = 0;
        $coll = $this->_groupCollection
            ->addFieldToFilter('customer_group_code', 'General');
        foreach ($coll as $key => $value) {
            if ($value->getCustomerGroupCode() == 'General') {
                $groupId = $value->getCustomerGroupId();
            }
        }
        if (!$groupId) {
            $groupId = 1;
            $coll = $this->_groupCollection;
            foreach ($coll as $key => $value) {
                $groupId = $value->getId();
            }
        }
        return $groupId;
    }

    /**
     * Get Customer Session
     *
     * @return void
     */
    public function getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * Get Customer Id
     *
     * @return void
     */
    public function getCustomerId()
    {
        $customerId= $this->getCustomerSession()->getCustomerId();
        if (isset($customerId) && !empty($customerId)) {
            if (!$this->registry->registry('sub_sellerid')):
                $this->registry->register('sub_sellerid', $customerId);
            endif;
            return $customerId;
        } else {
                 $subsellerId=$this->registry->registry('sub_sellerid');
            if ($subsellerId) {
                return $subsellerId;
            }
        }
    }

    /**
     * Get Sub Account By Id.
     *
     * @param int $id
     *
     * @return collection object
     */
    public function getSubAccountById($id)
    {
        $subAccount = $this->_subAccountRepository->get($id);
        return $subAccount;
    }
    
    /**
     * GetCurrentSubAccount.
     *
     * @return Webkul\SellerSubAccount\Model\SubAccount
     */
    public function getCurrentSubAccount()
    {
        $customerId = $this->getCustomerId();
        return $this->_subAccountRepository->getActiveByCustomerId($customerId);
    }

    /**
     * GetSubAccountSellerId.
     *
     * @return int
     */
    public function getSubAccountSellerId()
    {
        return $this->getCurrentSubAccount()->getSellerId();
    }

    /**
     * Get Sub Account By Id.
     *
     * @return bool
     */
    public function isSubAccount()
    {
        $customerId = $this->getCustomerId();
        $subAccount = $this->_subAccountRepository->getByCustomerId($customerId);
        if ($subAccount->getId()) {
            return 1;
        }
        return 0;
    }

    /**
     * IsActive.
     *
     * @return bool
     */
    public function isActive()
    {
        $customerId = $this->getCustomerId();
        $subAccount = $this->_subAccountRepository->getByCustomerId($customerId);
        if ($subAccount->getStatus()) {
            return 1;
        }
        return 0;
    }

    /**
     * Get Customer By Id.
     *
     * @param int $customerId
     *
     * @return collection object
     */
    public function getCustomerById($customerId)
    {
        $customer = $this->_customerRepository->getById($customerId);
        return $customer;
    }

    /**
     * Prepare Marketplace Mapped Labels.
     *
     * @return array
     */
    public function getMappedLabelsArr()
    {
        return [
          'marketplace/order/history' =>  'Manage Orders',
          'marketplace/order/view' => 'Manage Orders',
          'marketplace/product/productlist' => 'View Products',
          'marketplace/product/add' => 'Manage Products',
          'marketplace/account/dashboard' => 'View Dashboard',
          'marketplace/account/editprofile' => 'Manage Profile',
          'marketplace/product_attribute/new' => 'Create Configurable Product Attribute',
          'marketplace/transaction/history' => 'Manage Transaction',
          'marketplace/order/shipping' => 'Manage Order pdf header information'
        ];
    }
    /**
     * GetAllPermissionTypes.
     *
     * @return array
     */
    public function getAllPermissionTypes()
    {
        $options = [];
        $labelArr = $this->getMappedLabelsArr();
        $modules = $this->_moduleList->getNames();
        $dispatchResult = new \Magento\Framework\DataObject($modules);
        $modules = $dispatchResult->toArray();
        sort($modules);
    
        foreach ($modules as $moduleName) {
            if (strpos($moduleName, 'Webkul') !== false && ($moduleName !== 'Webkul_SellerSubAccount')) {
                $controllersList = $this->_controllersRepository->getByModuleName($moduleName);
                foreach ($controllersList as $key => $value) {
                    $path = $value['controller_path'];
                    $label = $value['label'];
                    if (array_key_exists($path, $labelArr)) {
                        $label = $labelArr[$path];
                    }
                    $options[$path] = __($label);
                }
            }
        }
        foreach ($modules as $moduleName) {
            if (strpos($moduleName, 'Webkul_MpRmaSystem') !== false && ($moduleName !== 'Webkul_SellerSubAccount')) {
                $options["mprmasystem/seller/allrma"] = __("Marketplace RMA");
            }
        }
        if (!$this->helperData->getIsSeparatePanel()) {
            unset($options['marketplace/account/customer']);
            unset($options['marketplace/account/review']);
        }
        if (empty($options)) {
            return $this->getPermissionTypeArr();
        }
        return $options;
    }

    /**
     * GetAllAllowedActions to get all allowed subaccount actions.
     *
     * @return array
     */
    public function getAllAllowedActions()
    {
        $allAllowedActions = [];
        $subAccount = $this->getCurrentSubAccount();
        if ($subAccount->getId()) {
            $allowedPermissionType = explode(',', $subAccount->getPermissionType());
            $adminPermission = $this->getSellerPermissionByCustomerId();
            $commonPermission = array_intersect($allowedPermissionType, $adminPermission);
            $mappedControllers = $this->getAllPermissionTypes();
            $mappedControllers = array_change_key_case($mappedControllers, CASE_LOWER);
            foreach ($commonPermission as $path) {
                $path = strtolower($path);
                if (!empty($mappedControllers[$path])) {
                    $allAllowedActions[$path] = $mappedControllers[$path];
                }
            }
        }
        // if (!empty($allAllowedActions['marketplace/product/add'])) {
        //     $allAllowedActions['marketplace/product/productlist'] = __('View Products');
        // }
        return $allAllowedActions;
    }

    /**
     * Manage SubAccounts
     *
     * @return void
     */
    public function manageSubAccounts()
    {
        return $this->scopeConfig->getValue(
            'sellersubaccount/general_settings/manage_sub_accounts',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * Get Customer Model By Id.
     *
     * @param int $customerId
     *
     * @return collection object
     */
    public function getCustomerModelById($customerId)
    {
        $customer = $this->customerModFactory->create()->load($customerId);
        return $customer;
    }
    
    /**
     * Save Customer Data.
     *
     * @param array $customerData
     * @param integer $customerId
     * @param integer $websiteId
     * @return void
     */
    public function saveCustomerData($customerData, $customerId = 0, $websiteId = 0)
    {
        if (!empty($customerData)) {
            $customerData['customerId'] = $this->getCustomerIdByEmail($customerData);
            if (!empty($customerData['customerId'])) {
                $customerId = $customerData['customerId'];
            }
            try {
                // optional fields might be set in request for future processing
                // by observers in other modules
                $customerData['website_id'] = $websiteId;
                $customerData['group_id'] = $this->getAccountGroup();
                $customerData['disable_auto_group_change'] = 1;
                
                if (array_key_exists('prefix', $customerData)) {
                    if (isset($customerData['prefix'])) {
                         $customerData['prefix'] = $customerData['prefix'];
                    }
                }
                if (array_key_exists('middlename', $customerData)) {
                    if (isset($customerData['middlename'])) {
                         $customerData['middlename'] = $customerData['middlename'];
                    }
                }
                if (array_key_exists('suffix', $customerData)) {
                    if (isset($customerData['suffix'])) {
                         $customerData['suffix'] = $customerData['suffix'];
                    }
                }
                if (array_key_exists('company', $customerData)) {
                    if (isset($customerData['company'])) {
                         $customerData['company'] = $customerData['company'];
                    }
                }

                if (array_key_exists('dob', $customerData)) {
                    if (isset($customerData['dob'])) {
                         $customerData['dob'] = $customerData['dob'];
                    }
                }

                if (array_key_exists('gender', $customerData)) {
                    if (isset($customerData['gender'])) {
                         $customerData['gender'] = $customerData['gender'];
                    }
                }
                
                if (isset($customerData['taxvat'])) {
                    $customerData['taxvat'] = $customerData['taxvat'];
                }
                
                if (array_key_exists('address', $customerData)) {
                    if (array_key_exists('country_id', $customerData)) {

                        if (isset($customerData['country_id'])) {
                            $customerData['address']['country_id']=$customerData['country_id'];
                        }
                    }

                    if (array_key_exists('company', $customerData)) {

                        if (isset($customerData['company'])) {
                            $customerData['address']['company']=$customerData['company'];
                        }
                    }

                    if (array_key_exists('city', $customerData)) {
                        
                        if (isset($customerData['city'])) {
                            $customerData['address']['city']=$customerData['city'];
                        }
                    }

                    if (array_key_exists('postalcode', $customerData)) {
                        
                        if (isset($customerData['postalcode'])) {
                            $customerData['address']['postalcode']=$customerData['postalcode'];
                        }
                    }

                    if (array_key_exists('region_id', $customerData)) {
                        
                        if (isset($customerData['region_id'])) {
                            $customerData['address']['region_id']=$customerData['region_id'];
                        }
                    }

                    if (array_key_exists('telephone', $customerData)) {
                        
                        if (isset($customerData['telephone'])) {
                            $customerData['address']['telephone']=$customerData['telephone'];
                        }
                    }
                    
                    if (array_key_exists('fax', $customerData)) {
                        
                        if (isset($customerData['fax'])) {
                            $customerData['address']['fax']=$customerData['fax'];
                        }
                    }
                    if (array_key_exists('vat_id', $customerData)) {
                        
                        if (isset($customerData['vat_id'])) {
                            $customerData['address']['vat_id']=$customerData['vat_id'];
                        }
                    }
                    
                    if ($this->isReqAddressEnable()) {
                        $customerData['create_address']=1;
                    } else {
                        $customerData['create_address']=0;
                    }
                   
                    $customerData['default_billing']=1;
                    $customerData['default_shipping']=1;
                }
              
                $customerData['confirmation'] = '';
                $customerData['sendemail_store_id'] = 1;

                if ($this->dobMandatory() && array_key_exists('dob', $customerData)) {
                    $birthday = $customerData['dob'];
                    $timestamp = strtotime($birthday);
                    $customerDob = date("Y-m-d", $timestamp);
                    $customerData['dob'] = $customerDob;
                } else {

                    $customerDob = date("Y-m-d");
                    $customerData['dob'] = $customerDob;
                }
                if (array_key_exists('address', $customerData)) {
                    $addresses=$this->createAddresses($customerData);
                    $addresses = $addresses === null ? [] : [$addresses];
                    $customer->setAddresses($addresses);
                }
                if ($customerId) {
                    $currentCustomer = $this->_customerRepository->getById($customerId);
                    $customerData = array_merge(
                        $this->_customerMapper->toFlatArray($currentCustomer),
                        $customerData
                    );
                    $customerData['id'] = $customerId;
                }
                /** @var CustomerInterface $customer */
                $customer = $this->_customerFactory->create();
                $this->_dataObjectHelper->populateWithArray(
                    $customer,
                    $customerData,
                    \Magento\Customer\Api\Data\CustomerInterface::class
                );
                // Save customer
                if ($customerId) {
                    $this->_customerRepository->save($customer);

                    $this->getEmailNotification()->credentialsChanged(
                        $customer,
                        $currentCustomer->getEmail()
                    );
                } else {
                    $customer = $this->_accountManagement->createAccount($customer);
                    $customerId = $customer->getId();
                }
                $this->customerModFactory->create()
                        ->load($customer->getId())
                        ->setGroupId($customerData['group_id'])
                        ->save();
            } catch (\Exception $e) {
                return ['error'=>1, 'message'=>$e->getMessage(), 'customer_id'=>$customerId];
            }
        }
        return ['error'=>0, 'customer_id'=>$customerId];
    }

    /**
     * Get Customer Id By Email Id
     *
     * @param string $customerData
     * @return int|null
     */
    public function getCustomerIdByEmail(&$customerData)
    {
        try {
            $customerInfo = $this->_customerRepository->get($customerData['email']);
          
            $customerData['customerId'] = (int)$customerInfo->getId();
        } catch (\Exception $e) {
            $customerData['customerId'] = "";
        }
        return $customerData['customerId'];
    }
    
    /**
     * Create Addresses
     *
     * @param array $customerData
     * @return void
     */
    public function createAddresses($customerData)
    {
        if (!isset($customerData['create_address'])) {
            return null;
        }

        $addressForm = $this->formFactory->create('customer_address', 'customer_register_address');
        $allowedAttributes = $addressForm->getAllowedAttributes();

        $addressData = [];

        $regionDataObject = $this->regionDataFactory->create();
        $userDefinedAttr = isset($customerData['address']) ?: [];
        foreach ($allowedAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if ($attribute->isUserDefined()) {
                $value = array_key_exists($attributeCode, $userDefinedAttr) ? $userDefinedAttr[$attributeCode] : null;
            } else {
                if (array_key_exists($attributeCode, $customerData)) {
                     $value = $customerData[$attributeCode];
                }
            }

            if ($value === null) {
                continue;
            }
            switch ($attributeCode) {
                case 'region_id':
                    $regionDataObject->setRegionId($value);
                    break;
                case 'region':
                    $regionDataObject->setRegion($value);
                    break;
                default:
                    $addressData[$attributeCode] = $value;
            }
        }
        $addressData = $addressForm->compactData($addressData);
        unset($addressData['region_id'], $addressData['region']);

        $addressDataObject = $this->addressDataFactory->create();
        $this->_dataObjectHelper->populateWithArray(
            $addressDataObject,
            $addressData,
            \Magento\Customer\Api\Data\AddressInterface::class
        );
        $addressDataObject->setRegion($regionDataObject);

        $addressDataObject->setIsDefaultBilling(
            $customerData['default_billing']
        )->setIsDefaultShipping(
            $customerData['default_shipping']
        );
        return $addressDataObject;
    }

    /**
     * Is Request Address Enable
     *
     * @return boolean
     */
    public function isReqAddressEnable()
    {
        $ent = $this->scopeConfig->getValue(
            'sellersubaccount/general_settings/manage_add_show',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $ent;
    }

    /**
     * Is Customer Field Req
     *
     * @param string $fieldName
     * @return boolean
     */
    public function isCustomerFieldReq($fieldName)
    {
        $field = $this->scopeConfig->getValue(
            'customer/address/'.$fieldName,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($field == "req") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Dob Mandatory
     *
     * @return void
     */
    public function dobMandatory()
    {
        $dob = $this->scopeConfig->getValue(
            'customer/address/dob_show',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($dob == "req") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gender mandatory
     *
     * @return void
     */
    public function genderMandatory()
    {
        $gender = $this->scopeConfig->getValue(
            'customer/address/gender_show',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($gender == "req") {
            return true;
        } else {
            return false;
        }
    }
    /**
     * Save Customer Group Data.
     *
     * @param integer $customerId
     * @param integer $websiteId
     * @return void
     */
    public function saveCustomerGroupData($customerId = 0, $websiteId = 0)
    {
        if ($customerId) {
            try {
                // optional fields might be set in request for future processing
                // by observers in other modules
                $customerData['group_id'] = $this->getAccountGeneralGroup();
                $currentCustomer = $this->_customerRepository->getById($customerId);
                $customerData = array_merge(
                    $this->_customerMapper->toFlatArray($currentCustomer),
                    $customerData
                );
                $customerData['id'] = $customerId;
                /** @var CustomerInterface $customer */
                $customer = $this->_customerFactory->create();
                $this->_dataObjectHelper->populateWithArray(
                    $customer,
                    $customerData,
                    \Magento\Customer\Api\Data\CustomerInterface::class
                );
                // Save customer
                $this->_customerRepository->save($customer);
            } catch (\Exception $e) {
                return ['error'=>1, 'message'=>$e->getMessage()];
            }
        }
        return ['error'=>0, 'customer_id'=>$customerId];
    }

    /**
     * Get email notification
     *
     * @return EmailNotificationInterface
     */
    public function getEmailNotification()
    {
        if (!($this->emailNotification instanceof EmailNotificationInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                EmailNotificationInterface::class
            );
        } else {
            return $this->emailNotification;
        }
    }

    /**
     * Get child blocks
     *
     * @param string $blockName
     * @return array
     */
    public function getAllChildBlocksByBlockName($blockName)
    {
        $resultPage = $this->resultPageFactory->create();
        $blockInstance = $resultPage->getLayout()->getBlock($blockName);
        return $blockInstance->getChildNames();
    }

    /**
     * Is child blocks allowed
     *
     * @return boolean
     */
    public function isAllowedChildMenu()
    {
        $shippingMenu = $this->getAllChildBlocksByBlockName(
            'layout2_seller_account_navigation_shipping_menu'
        );
        $paymentMenu = $this->getAllChildBlocksByBlockName(
            'layout2_seller_account_navigation_payment_menu'
        );
        $flag = false;
        if (count($shippingMenu)) {
            $actionNames = $this->getAllShippingActions();
            foreach ($actionNames as $actionName) {
                if ($this->helperData->isAllowedAction($actionName)) {
                    return true;
                }
            }
        }
        if (count($paymentMenu)) {
            $actionNames = $this->getAllPaymentActions();
            foreach ($actionNames as $actionName) {
                if ($this->helperData->isAllowedAction($actionName)) {
                    return true;
                }
            }
        }
        return $flag;
    }

    /**
     * Get All Shipping Actions
     *
     * @return void
     */
    public function getAllShippingActions()
    {
        return [
            'mpups/shipping/view',
            'mpshipping/shippingset/view',
            'mpshipping/shipping/view',
            'mpdhl/shipping/view',
            'endicia/account/config',
            'endicia/account/manage',
            'baseshipping/shipping',
            'mpfrenet/shipping/index',
            'auspost/shipping/view',
            'mpfastway/shipping/view',
            'canadapost/shipping/view/',
            'canadapost/shipping/view',
            'mpdelhivery/orders/index',
            'mparamex/shipping/view',
            'multiship/shipping/view',
            'mpfedex/shipping/view',
            'freeshipping/shipping/view',
            'mpfixrate/shipping/view',
            'mpusps/shipping/view',
            'easypost/shipping/view',
            'timedelivery/account/index'
        ];
    }

    /**
     * Get All Payment Actions
     *
     * @return void
     */
    public function getAllPaymentActions()
    {
        return [
            'mpbraintree/braintreeaccount/index',
            'mpmoip/seller/connect',
            'iyzico/onboard/merchant',
            'mpmasspay/paypal/index',
            'mpcitruspayment/sellerdetail/index',
            'mpcitruspayment/sellerdetail',
            'mercadopago/seller/permission',
            'mpmangopay/bankdetail',
            'mpmangopay/sellerkyc',
            'mpmangopay/seller/transaction',
            'mpstripe/seller/connect'
        ];
    }

    /**
     * Get Controller Mapped Permissions Seller SubAccount
     *
     * @return void
     */
    public function getControllerMappedPermissionsSellerSubAccount()
    {
        return [
            'marketplace/account/askquestion' => 'marketplace/account/dashboard',
            'marketplace/account_dashboard/tunnel' => 'marketplace/account/dashboard',
            'marketplace/account/chart' => 'marketplace/account/dashboard',
            'marketplace/account/becomesellerPost' => 'marketplace/account/becomeseller',
            'marketplace/account/deleteSellerBanner' => 'marketplace/account/editProfile',
            'marketplace/account/deleteSellerLogo' => 'marketplace/account/editProfile',
            'marketplace/account/editProfilePost' => 'marketplace/account/editProfile',
            'marketplace/account/rewriteUrlPost' => 'marketplace/account/editProfile',
            'marketplace/account/savePaymentInfo' => 'marketplace/account/editProfile',
            'mprmasystem/seller/rma' => 'mprmasystem/seller/rma'
        ];
    }

    /**
     * Get Seller Premission For Sub seller by admin
     *
     * @return void
     */
    public function getSellerPermissionForSubSellerByAdmin()
    {
        $individualSellerPermission = $this->_sellerModel->create()
        ->getCollection()
        ->addFieldToFilter('seller_id', $this->getCustomerId())
        ->getFirstItem()
        ->getSubAccountPermission();
        
        if ($individualSellerPermission != null) {
            return explode(',', $individualSellerPermission);
        } else {
            $list = $this->scopeConfig->getValue(
                'sellersubaccount/sub_account_permission/manage_sub_account_permission',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ($list == null) {
                $list = '';
            }
            return explode(',', $list);
        }
    }

    /**
     * Get Seller Permission By CustomerId
     *
     * @return void
     */
    public function getSellerPermissionByCustomerId()
    {
        $sellerId = $this->getSubAccountSellerId();
        $individualSellerPermission = $this->_sellerModel->create()
        ->load($sellerId)
        ->getSubAccountPermission();
        if ($individualSellerPermission != null) {
            return explode(',', $individualSellerPermission);
        } else {
            $list = $this->scopeConfig->getValue(
                'sellersubaccount/sub_account_permission/manage_sub_account_permission',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            return explode(',', $list);
        }
    }

    /**
     * IsAllowForBecomeSeller
     *
     * @return boolean
     */
    public function isAllowForBecomeSeller()
    {
        $flag = true;
        if ($this->isSubAccount() && !$this->isActive()) {
            $flag = false;
        }
        return $flag;
    }

    /**
     * Checking the user is seller or not
     *
     * @param  int $sellerId
     * @return bool $sellerStatus
     */
    public function isSeller($sellerId)
    {
        $sellerStatus = 0;
        $model = $this->helperData->getSellerCollectionObj($sellerId);
        foreach ($model as $value) {
            if ($value->getIsSeller() == 1) {
                $sellerStatus = $value->getIsSeller();
            }
        }
        return $sellerStatus;
    }

    /**
     * Prepare Marketplace Permission type
     *
     * @return array
     */
    public function getPermissionTypeArr()
    {
        return [
          'marketplace/account/dashboard' => __('View Dashboard'),
          'marketplace/account/editprofile' => __('Manage Profile'),
          'marketplace/product_attribute/new' => __('Create Configurable Product Attribute'),
          'marketplace/product/add' => __('Manage Products'),
          'marketplace/product/productlist' => __('View Products'),
          'marketplace/transaction/history' => __('Manage Transaction'),
          'marketplace/order/shipping' => __('Manage Order pdf header information'),
          'marketplace/order/history' => __('Manage Orders'),
          'marketplace/account/earning' => __('Earnings')
        ];
    }
}
