<?php declare(strict_types=1);
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpFixedRateshipping
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpFixedRateshipping\Controller\Shipping;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;

class Index extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSessionFactory;

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
     * @var \Webkul\Mpfixrateshipping\Helper\Data
     */
    protected $_currentHelper;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Magento\Customer\Model\Customer\Mapper $customerMapper
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Magento\Customer\Model\Url $url
     * @param \Webkul\MpFixedRateshipping\Helper\Data $currentHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerInterfaceFactory $customerDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Customer\Model\Url $url,
        \Webkul\MpFixedRateshipping\Helper\Data $currentHelper
    ) {
        $this->_customerSessionFactory = $customerSessionFactory;
        $this->url = $url;
        $this->_customerRepository = $customerRepository;
        $this->_customerMapper = $customerMapper;
        $this->_customerDataFactory = $customerDataFactory;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_currentHelper = $currentHelper;
        parent::__construct($context);
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->_customerSessionFactory->create();
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->url
                ->getLoginUrl();

        if (!$this->_customerSessionFactory->create()->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Default customer account page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $customerData = $this->getRequest()->getParams();
            $fixRateUpTo = (int)str_replace(',', '', $customerData['mpshipping_fixrate_upto']);
            $customerId = $this->_getSession()->getCustomerId();
            $savedData = $this->_customerRepository->getById($customerId);
            $baseCurrencyCode = $this->_currentHelper->getBaseCurrencyCode();
            $currentCurrencyCode = $this->_currentHelper->getCurrentCurrencyCode();
            $allowedCurrencies = $this->_currentHelper->getAllowedCurrencies();
            $error = false;
            $rate = $this->_currentHelper->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
            if (!empty($rate) &&
                ($customerData['mpshipping_fixrate'] != '' && $customerData['mpshipping_fixrate_upto'] != '')
            ) {
                $customerData['mpshipping_fixrate'] = $customerData['mpshipping_fixrate'] / $rate[$currentCurrencyCode];
                $customerData['mpshipping_fixrate_upto'] = $fixRateUpTo / $rate[$currentCurrencyCode];
            }

            if ($customerData['mpshipping_fixrate'] <0 || $customerData['mpshipping_fixrate_upto'] <0) {
                $error = true;
                $this->messageManager->addError(__('Prices can\'t be negative.'));
            }

            if (!$error) {
                $customer = $this->_customerDataFactory->create();
                $customerData = array_merge(
                    $this->_customerMapper->toFlatArray($savedData),
                    $customerData
                );
                $customerData['id'] = $customerId;
                $this->_dataObjectHelper->populateWithArray(
                    $customer,
                    $customerData,
                    \Magento\Customer\Api\Data\CustomerInterface::class
                );
                $this->_customerRepository->save($customer);
                $this->messageManager->addSuccess(__('Fixed Rate Shipping details saved successfully.'));
            }

            return $this->resultRedirectFactory->create()
                ->setPath(
                    'mpfixrate/shipping/view',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
        }
    }
}
