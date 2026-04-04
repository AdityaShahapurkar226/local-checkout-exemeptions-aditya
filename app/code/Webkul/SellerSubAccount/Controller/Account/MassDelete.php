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

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\SellerSubAccount\Helper\Data as HelperData;
use Webkul\SellerSubAccount\Model\ResourceModel\SubAccount\CollectionFactory;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

/**
 * Class SellerSubAccount MassDelete.
 */
class MassDelete extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\CsrfAwareActionInterface
{
    /**
     * @var Filter
     */
    public $_filter;

    /**
     * @var HelperData
     */
    public $_helper;

    /**
     * @var CollectionFactory
     */
    public $_collectionFactory;

    /**
     * @param Context           $context
     * @param Filter            $filter
     * @param HelperData        $helper
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        HelperData $helper,
        CollectionFactory $collectionFactory
    ) {
        $this->_filter = $filter;
        $this->_helper = $helper;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        try {
            $sellerId = $this->_helper->getCustomerId();
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            $countRecord = $collection->getSize();
            foreach ($collection as $item) {
                if ($item->getSellerId() == $sellerId) {
                    $customerId = $item->getCustomerId();
                    $item->delete();
                    $this->_helper->saveCustomerGroupData(
                        $customerId
                    );
                }
            }
            $this->messageManager->addSuccess(
                __(
                    'A total of %1 record(s) have been deleted.',
                    $countRecord
                )
            );

            /** @var \Magento\Framework\Controller\Result\RedirectFactory $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            return $this->resultRedirectFactory->create()->setPath(
                'sellersubaccount/account/manage',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());

            return $this->resultRedirectFactory->create()->setPath(
                'sellersubaccount/account/manage',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }
    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
