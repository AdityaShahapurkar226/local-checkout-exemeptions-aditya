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

namespace Webkul\SellerSubAccount\Controller\Account;

use Magento\Customer\Model\EmailNotificationInterface;
use Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\CollectionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\RequestInterface;
use Webkul\SellerSubAccount\Model\SubAccount;
use Webkul\SellerSubAccount\Api\SubAccountRepositoryInterface;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Webkul\SellerSubAccount\Helper\Data as HelperData;
use Magento\Framework\Controller\Result\ForwardFactory;

/**
 * Webkul SellerSubAccount Account Save Controller.
 */
class Save extends \Webkul\SellerSubAccount\Controller\AbstractSubAccount
{
    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var Collection
     */
    protected $collectionFactory;

    /**
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var SubAccount
     */
    protected $_subAccount;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var SubAccountRepositoryInterface
     */
    protected $_subAccountRepository;

    /**
     * @var MarketplaceHelper
     */
    protected $_marketplaceHelper;

    /**
     * @var HelperData
     */
    protected $_helper;

    /**
     * Description
     *
     * @param Context                                    $context
     * @param PageFactory                                $resultPageFactory
     * @param ForwardFactory                             $resultForwardFactory
     * @param \Magento\Customer\Model\Url                $url
     * @param \Magento\Customer\Model\Session            $customerSession
     * @param FormKeyValidator                           $formKeyValidator
     * @param SubAccount                                 $subAccount
     * @param Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param SubAccountRepositoryInterface              $subAccountRepository
     * @param MarketplaceHelper                          $marketplaceHelper
     * @param HelperData                                 $helper
     * @param CollectionFactory                          $collectionFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $customerSession,
        FormKeyValidator $formKeyValidator,
        SubAccount $subAccount,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        SubAccountRepositoryInterface $subAccountRepository,
        MarketplaceHelper $marketplaceHelper,
        HelperData $helper,
        CollectionFactory $collectionFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_url = $url;
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_subAccount = $subAccount;
        $this->_date = $date;
        $this->_subAccountRepository = $subAccountRepository;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_helper = $helper;
        $this->collectionFactory = $collectionFactory;
        parent::__construct(
            $context,
            $resultPageFactory,
            $resultForwardFactory,
            $url,
            $customerSession,
            $formKeyValidator,
            $subAccount,
            $date,
            $subAccountRepository,
            $marketplaceHelper,
            $helper
        );
    }

    /**
     * Execute
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath(
                        'sellersubaccount/account/manage',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
                $postData = $this->getRequest()->getPostValue();
                if (empty($postData['permission_type'])) {
                    $postData['permission_type'] = [];
                }
                if (empty($postData['status'])) {
                    $postData['status'] = 0;
                }
                $sellerId = $this->_helper->getCustomerId();
                $id = isset($postData['id'])
                    ? $postData['id']
                    : null;
                if (!empty($id)) {
                    // Check If sub account does not exists
                    $subAccount = $this->_subAccountRepository->get($id);
                    if ($subAccount->getId()) {
                        if ($subAccount->getSellerId() == $sellerId) {
                            $this->checkAndSaveCustomerData($id, $postData, $subAccount);
                        } else {
                            $this->messageManager->addError(
                                __('You are not authorized to update this sub account.')
                            );
                        }
                    } else {
                        $this->messageManager->addError(
                            __('Sub Account does not exist.')
                        );
                    }
                } else {
                    if ($this->validateEmail($postData)) {
                        $result = $this->_helper->saveCustomerData($postData);
                        if (!empty($result['error']) && $result['error'] == 1) {
                            $this->messageManager->addError(
                                $result['message']
                            );
                            return $this->resultRedirectFactory->create()->setPath(
                                'sellersubaccount/account/manage',
                                ['_secure' => $this->getRequest()->isSecure()]
                            );
                        } else {
                            $customerId = $result['customer_id'];
                        }
                        $value = $this->_subAccount;
                        $value->setSellerId($sellerId);
                        $value->setCustomerId($customerId);
                        $value->setPermissionType(implode(',', $postData['permission_type']));
                        $value->setStatus($postData['status']);
                        $value->setCreatedDate($this->_date->gmtDate());
                        $id = $value->save()->getId();
                        $this->messageManager->addSuccess(
                            __('SubAccount was succesfully created')
                        );
                        $this->messageManager->addNotice(
                            __('Please check your email %1 for your login details', $postData['email'])
                        );
                    } else {
                        $this->messageManager->addError(
                            __('Customer with this email registered already')
                        );
                    }
                    
                }
                return $this->resultRedirectFactory->create()->setPath(
                    'sellersubaccount/account/edit',
                    ['id'=>$id, '_secure' => $this->getRequest()->isSecure()]
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $this->resultRedirectFactory->create()->setPath(
                    'sellersubaccount/account/manage',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'sellersubaccount/account/manage',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Check and save customer data
     *
     * @param int $id
     * @param array $postData
     * @param collection $subAccount
     * @return void
     */
    public function checkAndSaveCustomerData($id = null, $postData = null, $subAccount = null)
    {
            $result = $this->_helper->saveCustomerData(
                $postData,
                $subAccount->getCustomerId(),
                $this->_marketplaceHelper->getWebsiteId()
            );
        if (!empty($result['error']) && $result['error'] == 1) {
            $this->messageManager->addError(
                $result['message']
            );
            return $this->resultRedirectFactory->create()->setPath(
                'sellersubaccount/account/manage',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } else {
                $customerId = $result['customer_id'];
        }
            $value = $this->_subAccount->load($id);
            $value->setPermissionType(
                implode(',', $postData['permission_type'])
            );
        
            $value->setStatus($postData['status']);
            $value->save();
            $this->messageManager->addSuccess(
                __('Sub Account was Updated successfully.')
            );
    }
    /**
     * For checking Duplicacy of email
     *
     * @param array $postData
     *
     * @return bool
     */
    public function validateEmail($postData)
    {
        $collection = $this->collectionFactory->create();
        $joinTable = $collection->getTable('customer_grid_flat');
        $collection->getSelect()->join(
            $joinTable.' as cgf',
            'main_table.customer_id = cgf.entity_id',
            [
                'name' => 'name',
                'email' => 'email',
                'customer_created_at' => 'created_at'
            ]
        );
        $collection->addFieldToFilter('email', $postData['email']);
        if ($collection->getSize() > 0) {
            return false;
        }
        return true;
    }
}
