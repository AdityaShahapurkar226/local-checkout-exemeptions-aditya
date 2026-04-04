<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpApi
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpApi\Api;

interface SellerManagementInterface
{
    /**
     * Interface for specific seller details.
     *
     * @api
     *
     * @param int $id Seller Id
     * @param int $storeId Store id
     *
     * @return Magento\Framework\Api\SearchResults
     */
    public function getSeller($id, $storeId = 0);

    /**
     * Get seller details.
     *
     * @api
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getSellerList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Get seller products.
     *
     * @api
     *
     * @param int $id Seller Id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getSellerProducts($id);

    /**
     * Interface for specific seller details.
     *
     * @api
     *
     * @param int $id Seller Id
     *
     * @return Magento\Framework\Api\SearchResults
     */
    public function getSellerSalesList($id);

    /**
     * Interface for getting seller sales details.
     *
     * @api
     *
     * @param int $id Seller Id
     *
     * @return Magento\Framework\Api\SearchResults
     */
    public function getSellerSalesDetails($id);

    /**
     * Interface for creating seller order invoice.
     *
     * @api
     *
     * @param int $id Seller Id
     * @param int $orderId Order Id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function createInvoice($id, $orderId);

    /**
     * Interface to vew invoice.
     *
     * @api
     *
     * @param int $id Seller Id
     * @param int $orderId Order Id
     * @param int $invoiceId Invoice Id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function viewInvoice($id, $orderId, $invoiceId);

    /**
     * Interface for cancel order.
     *
     * @api
     *
     * @param int $id      Seller Id
     * @param int $orderId Order Id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function cancelOrder($id, $orderId);

    /**
     * Interface for creating credit memo.
     *
     * @api
     *
     * @param int $id Seller Id
     * @param int $invoiceId Invoice Id
     * @param int $orderId Order Id
     * @param Webkul\MpApi\Api\Data\CreditMemoInterface $creditMemo Credit Memo Object
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function createCreditmemo($id, $invoiceId, $orderId, $creditMemo);

    /**
     * Interface to view credit memp.
     *
     * @api
     *
     * @param int $id Seller Id
     * @param int $orderId
     * @param int $creditmemoId
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function viewCreditmemo($id, $orderId, $creditmemoId);

    /**
     * Interface for generating shipment.
     *
     * @api
     *
     * @param int $id Seller Id
     * @param int $orderId
     * @param string $trackingId
     * @param string $carrier
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function ship($id, $orderId, $trackingId, $carrier);

    /**
     * Interface to view shipment.
     *
     * @api
     *
     * @param int $id Seller Id
     * @param int $orderId
     * @param int $shipmentId
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function viewShipment($id, $orderId, $shipmentId);

    /**
     * Interface for mail to admin.
     *
     * @api
     *
     * @param int $id Seller Id
     * @param string $subject Subject
     * @param string $query Query
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function mailToAdmin($id, $subject, $query);

    /**
     * Interface for mail to seller.
     *
     * @api
     *
     * @param int $id Seller Id
     * @param string $subject Subject
     * @param string $query Query
     * @param int $productId Product Id
     * @param string $customerEmail Customer Email
     * @param string $customerName Customer Name
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function mailToSeller($id, $subject, $query, $productId, $customerEmail, $customerName);

    /**
     * Become partner .
     *
     * @api
     *
     * @param int $id Seller Id
     * @param string $shopUrl Shop Url
     * @param boolean $isSeller Is Seller
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function becomePartner($id, $shopUrl, $isSeller);

    /**
     * Get landing page data.
     *
     * @api
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getLandingPageData();

    /**
     * Get seller reviews .
     *
     * @api
     *
     * @param int $id Seller Id
     *
     * @return Magento\Framework\Api\SearchResults
     */
    public function getSellerReviews($id);

    /**
     * Get seller reviews .
     *
     * @api
     *
     * @param int $sellerId Seller Id
     * @param Webkul\MpApi\Api\Data\FeedbackInterface $feedback
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function makeSellerReview($sellerId, \Webkul\MpApi\Api\Data\FeedbackInterface $feedback);

    /**
     * Get review by Review Id
     *
     * @api
     *
     * @param int $reviewId Review id
     *
     * @return Magento\Framework\Api\SearchResults
     */
    public function getReview($reviewId);

    /**
     * Create seller account
     *
     * @api
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string $password
     * @param boolean $isSeller
     * @param string $profileurl
     * @param boolean $registered
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function createAccount(
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        $password,
        $isSeller,
        $profileurl,
        $registered
    );

    /**
     * Create product
     *
     * @api
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param int $id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function saveProduct(\Magento\Catalog\Api\Data\ProductInterface $product, $id);
}
