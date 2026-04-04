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
namespace Webkul\SellerSubAccount\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Webkul\SellerSubAccount\Model\SubAccount;
use Webkul\SellerSubAccount\Api\SubAccountRepositoryInterface;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Webkul\SellerSubAccount\Helper\Data as HelperData;

/**
 * Webkul SellerSubAccount AbstractSubAccount Controller
 */
abstract class AbstractSubAccount extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    public $_resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    public $resultForwardFactory;

    /**
     * @var SubAccount
     */
    public $_subAccount;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $_date;

    /**
     * @var SubAccountRepositoryInterface
     */
    public $_subAccountRepository;

    /**
     * @var MarketplaceHelper
     */
    public $_marketplaceHelper;

    /**
     * @var HelperData
     */
    public $_helperData;

    /**
     * @var \Magento\Framework\Registry
     */
    public $_coreRegistry = null;

    /**
     * @param Context                                    $context
     * @param PageFactory                                $resultPageFactory
     * @param ForwardFactory                             $resultForwardFactory
     * @param SubAccount                                 $subAccount
     * @param Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param SubAccountRepositoryInterface              $subAccountRepository
     * @param MarketplaceHelper                          $marketplaceHelper
     * @param HelperData                                 $helperData
     * @param \Magento\Framework\Registry                $coreRegistry
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        SubAccount $subAccount,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        SubAccountRepositoryInterface $subAccountRepository,
        MarketplaceHelper $marketplaceHelper,
        HelperData $helperData,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_subAccount = $subAccount;
        $this->_subAccountRepository = $subAccountRepository;
        $this->_date = $date;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_helperData = $helperData;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Marketplace::seller');
    }
}
