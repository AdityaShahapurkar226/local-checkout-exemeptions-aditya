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
namespace Webkul\SellerSubAccount\Controller;

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
 * Webkul SellerSubAccount AbstractSubAccount Controller
 */
abstract class AbstractSubAccount extends \Magento\Framework\App\Action\Action
{
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
        HelperData $helper
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_url = $url;
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_subAccount = $subAccount;
        $this->_subAccountRepository = $subAccountRepository;
        $this->_date = $date;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_helper = $helper;
        parent::__construct($context);
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
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }
}
