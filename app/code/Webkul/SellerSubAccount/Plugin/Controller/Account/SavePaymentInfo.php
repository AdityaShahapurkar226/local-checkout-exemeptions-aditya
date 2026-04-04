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
namespace Webkul\SellerSubAccount\Plugin\Controller\Account;

use Webkul\SellerSubAccount\Helper\Data as HelperData;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Webkul\Marketplace\Model\Seller;
use Magento\Framework\Message\ManagerInterface as MessageManager;

class SavePaymentInfo
{
    /**
     * @var HelperData
     */
    public $_helper;

    /**
     * @var RedirectFactory
     */
    public $_resultRedirectFactory;

    /**
     * @var FormKeyValidator
     */
    public $_formKeyValidator;

    /**
     * @var DateTime
     */
    public $_date;

    /**
     * @var Seller
     */
    public $_seller;

    /**
     * @var MessageManager
     */
    protected $_messageManager;

    /**
     * @param HelperData        $helper
     * @param RedirectFactory   $resultRedirectFactory
     * @param FormKeyValidator  $formKeyValidator
     * @param DateTime          $date
     * @param Seller            $seller
     * @param MessageManager    $messageManager
     */
    public function __construct(
        HelperData $helper,
        RedirectFactory $resultRedirectFactory,
        FormKeyValidator $formKeyValidator,
        DateTime $date,
        Seller $seller,
        MessageManager $messageManager
    ) {
        $this->_helper = $helper;
        $this->_resultRedirectFactory = $resultRedirectFactory;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_date = $date;
        $this->_seller = $seller;
        $this->_messageManager = $messageManager;
    }

    /**
     * Around Execute
     *
     * @param \Webkul\Marketplace\Controller\Account\SavePaymentInfo $block
     * @param \Closure $proceed
     *
     * @return int
     */
    public function aroundExecute(
        \Webkul\Marketplace\Controller\Account\SavePaymentInfo $block,
        \Closure $proceed
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            return $proceed();
        }
        $sellerId = $this->_helper->getSubAccountSellerId();
        if ($block->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($block->getRequest())) {
                    return $this->_resultRedirectFactory->create()->setPath(
                        '*/*/editProfile',
                        ['_secure' => $block->getRequest()->isSecure()]
                    );
                }
                $fields = $block->getRequest()->getParams();
                $autoId = '';
                $collection = $this->_seller
                ->getCollection()
                ->addFieldToFilter('seller_id', $sellerId);
                foreach ($collection as $value) {
                    $autoId = $value->getId();
                }

                $value = $this->_seller->load($autoId);
                $value->setPaymentSource($fields['payment_source']);
                $value->setPaymentSource($fields['payment_source']);
                $value->setUpdatedAt($this->_date->gmtDate());
                $value->save();
                $this->_messageManager->addSuccess(
                    __('Payment information was successfully saved')
                );

                return $this->_resultRedirectFactory->create()->setPath(
                    '*/*/editProfile',
                    ['_secure' => $block->getRequest()->isSecure()]
                );
            } catch (\Exception $e) {
                $this->_messageManager->addError($e->getMessage());

                return $this->_resultRedirectFactory->create()->setPath(
                    '*/*/editProfile',
                    ['_secure' => $block->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->_resultRedirectFactory->create()->setPath(
                '*/*/editProfile',
                ['_secure' => $block->getRequest()->isSecure()]
            );
        }
    }
}
