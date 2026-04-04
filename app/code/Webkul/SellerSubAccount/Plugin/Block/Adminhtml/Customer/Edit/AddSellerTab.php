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
namespace Webkul\SellerSubAccount\Plugin\Block\Adminhtml\Customer\Edit;

use Webkul\SellerSubAccount\Helper\Data as HelperData;
use Webkul\SellerSubAccount\Api\SubAccountRepositoryInterface;

class AddSellerTab
{
    /**
     * @var HelperData
     */
    public $_helper;

    /**
     * @var SubAccountRepositoryInterface
     */
    public $_subAccountRepository;

    /**
     * @param HelperData $helper
     * @param SubAccountRepositoryInterface $subAccountRepository
     */
    public function __construct(
        HelperData $helper,
        SubAccountRepositoryInterface $subAccountRepository
    ) {
        $this->_helper = $helper;
        $this->_subAccountRepository = $subAccountRepository;
    }

    /**
     * Around Can Show Tab.
     *
     * @param \Webkul\Marketplace\Block\Adminhtml\Customer\Edit\AddSellerTab $block
     * @param \Closure $proceed
     *
     * @return int
     */
    public function aroundCanShowTab(
        \Webkul\Marketplace\Block\Adminhtml\Customer\Edit\AddSellerTab $block,
        \Closure $proceed
    ) {
        $customerId = $block->getCustomerId();
        $subAccount = $this->_subAccountRepository->getByCustomerId($customerId);
        if (!$subAccount->getId()) {
            $result = $proceed();
            return $result;
        }
        if ($subAccount->getSellerId()) {
            return false;
        }

        return true;
    }
}
