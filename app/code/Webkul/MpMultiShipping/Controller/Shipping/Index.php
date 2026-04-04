<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMultiShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMultiShipping\Controller\Shipping;

use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Api\Data\CustomerInterface;

class Index extends AbstractAccount
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var \Magento\Customer\Model\Customer\Mapper
     */
    protected $_customerMapper;

    /**
     * @var CustomerInterfaceFactory
     */
    protected $_customerDataFactory;

    /**
     * @var DataObjectHelper
     */
    protected $_dataObjectHelper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Customer\Model\Customer\Mapper $customerMapper
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        $this->_customerRepository = $customerRepository;
        $this->_customerMapper = $customerMapper;
        $this->_customerDataFactory = $customerDataFactory;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Save Seller's configuration Data.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $selectedShipping = $this->getRequest()->getParam('shippingmethod');
            $customerData = ['allowed_shipping'=> json_encode($selectedShipping)];
            $customerId = $this->_customerSession->getCustomerId();
            $savedData = $this->_customerRepository->getById($customerId);
            $customer = $this->_customerDataFactory->create();
            $customerData = array_merge(
                $this->_customerMapper->toFlatArray($savedData),
                $customerData
            );
            $customerData['id'] = $customerId;
            $this->_dataObjectHelper->populateWithArray(
                $customer,
                $customerData,
                CustomerInterface::class
            );
            try {
                $this->_customerRepository->save($customer);
                $this->messageManager->addSuccess(__('Shipping details saved successfully.'));
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Can not save the records.'));
            }

            return $this->resultRedirectFactory->create()->setPath(
                'multiship/shipping/view',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        return $this->resultRedirectFactory->create()->setPath(
            'multiship/shipping/view',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
}
