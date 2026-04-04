<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\Plugin\Marketplace\Block\Product;

class Productlist
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Webkul\Marketplace\Model\SaleslistFactory
     */
    protected $saleslistFactory;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->saleslistFactory = $saleslistFactory;
    }

    /**
     * @param \Webkul\Marketplace\Block\Product\Productlist $subject
     * @param \Closure $proceed
     * @param string $productId
     * @return $data
     */
    public function aroundGetSalesdetail(
        \Webkul\Marketplace\Block\Product\Productlist $subject,
        \Closure $proceed,
        $productId = ''
    ) {
        $data = [
            'quantitysoldconfirmed' => 0,
            'quantitysoldpending' => 0,
            'amountearned' => 0,
            'clearedat' => 0,
            'quantitysold' => 0,
        ];
        $sum = 0;
        $arr = [];
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        $quantity = $this->saleslistFactory->create()
            ->getCollection()
            ->addFieldToFilter('seller_id', $customerId)
            ->addFieldToFilter(
                'mageproduct_id',
                $productId
            );

        foreach ($quantity as $rec) {
            $status = $rec->getCpprostatus();
            $data['quantitysold'] = $data['quantitysold'] + $rec->getMagequantity();
            if ($status == 1) {
                $data['quantitysoldconfirmed'] = $data['quantitysoldconfirmed'] + $rec->getMagequantity();
            } else {
                $data['quantitysoldpending'] = $data['quantitysoldpending'] + $rec->getMagequantity();
            }
        }

        $amountearned = $this->saleslistFactory->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'cpprostatus',
                            \Webkul\Marketplace\Model\Saleslist::PAID_STATUS_PENDING
                        )
                        ->addFieldToFilter('seller_id', $customerId)
                        ->addFieldToFilter(
                            'mageproduct_id',
                            $productId
                        );
        foreach ($amountearned as $rec) {
            $data['amountearned'] = $data['amountearned'] + $rec['actual_seller_amount'];
            $arr[] = $rec['created_at'];
        }
        $data['created_at'] = $arr;

        return $data;
    }
}
