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

namespace Webkul\SellerSubAccount\Controller\Adminhtml\Account;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\CollectionFactory;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Webkul\SellerSubAccount\Helper\Data as HelperData;

/**
 * Class SellerSubAccount MassDelete.
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    public $_filter;

    /**
     * @var CollectionFactory
     */
    public $_collectionFactory;

    /**
     * @var MarketplaceHelper
     */
    public $_marketplaceHelper;

    /**
     * @var HelperData
     */
    public $_helperData;

    /**
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     * @param MarketplaceHelper $marketplaceHelper
     * @param HelperData        $helperData
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        MarketplaceHelper $marketplaceHelper,
        HelperData $helperData
    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $sellerId = 0;
        try {
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            $countRecord = $collection->getSize();
            foreach ($collection as $item) {
                $sellerId = $item->getSellerId();
                $customerId = $item->getCustomerId();
                $item->delete();
                $this->_helperData->saveCustomerGroupData(
                    $customerId,
                    $this->_marketplaceHelper->getWebsiteId()
                );
            }
            $this->messageManager->addSuccess(
                __(
                    'A total of %1 record(s) have been deleted.',
                    $countRecord
                )
            );

            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            return $this->resultRedirectFactory->create()->setPath(
                'sellersubaccount/account/manage',
                [
                    'seller_id' => $sellerId,
                    '_secure' => $this->getRequest()->isSecure()
                ]
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());

            return $this->resultRedirectFactory->create()->setPath(
                'sellersubaccount/account/manage',
                [
                    'seller_id' => $sellerId,
                    '_secure' => $this->getRequest()->isSecure()
                ]
            );
        }
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Marketplace::seller');
    }
}
