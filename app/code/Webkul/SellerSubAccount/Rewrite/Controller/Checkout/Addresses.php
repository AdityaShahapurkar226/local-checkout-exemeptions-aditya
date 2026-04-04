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
namespace Webkul\SellerSubAccount\Rewrite\Controller\Checkout;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\View\Layout\BuilderFactory;

class Addresses extends \Magento\Multishipping\Controller\Checkout\Addresses
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

   /**
    * Constructor
    *
    * @param \Magento\Framework\App\Action\Context $context
    * @param \Magento\Customer\Model\Session $customerSession
    * @param CustomerRepositoryInterface $customerRepository
    * @param AccountManagementInterface $accountManagement
    * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
    * @param \Magento\Customer\Model\CustomerFactory $customerFactory
    * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
    * @param HttpContext $httpContext
    * @param BuilderFactory $builderFactory
    * @param \Magento\Framework\View\Result\PageFactory $pageFactory
    */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        HttpContext $httpContext,
        BuilderFactory $builderFactory,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement
        );

        $this->_customerSession = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->accountManagement = $accountManagement;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->customerFactory = $customerFactory;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->httpContext = $httpContext;
        $this->builderFactory = $builderFactory;
        $this->pageFactory = $pageFactory;
    }

    /**
     * Execute
     *
     * @return void
     */
    public function execute()
    {
        $sellerId = $this->httpContext->getValue('customer_id');
        $customer = $this->customerFactory->create()->load($sellerId);
        
        // If customer do not have addresses
        if (empty($customer->getAddresses()) || !$this->_getCheckout()->getCustomerDefaultShippingAddress()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/checkout_address/newShipping');
            return $resultRedirect;
        }

        $this->_getState()->unsCompleteStep(State::STEP_SHIPPING);

        $this->_getState()->setActiveStep(State::STEP_SELECT_ADDRESSES);
        if (!$this->_getCheckout()->validateMinimumAmount()) {
            $message = $this->_getCheckout()->getMinimumAmountDescription();
            $this->messageManager->addNotice($message);
        }
        $resultPage = $this->pageFactory->create();
        return $resultPage;
    }
}
