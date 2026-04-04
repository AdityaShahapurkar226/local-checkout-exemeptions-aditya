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

interface AdminManagementInterface
{
    /**
     * Get seller details.
     *
     * @api
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getSellerListForAdmin(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
    
    /**
     * Get seller products.
     *
     * @api
     *
     * @param int $id Seller id
     *
     * @return \Magento\Catalog\Api\Data\ProductSearchResultsInterface
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
    public function getSellerForAdmin($id);

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
     * Interface for order details.
     *
     * @api
     *
     * @param int $id Seller Id
     *
     * @return Magento\Framework\Api\SearchResults
     */
    public function getSellerSalesDetails($id);

    /**
     * Interface for paying the seller.
     *
     * @api
     *
     * @param int $sellerId Seller id
     * @param string $sellerPayReason Reason to pay seller
     * @param int $entityId Entity Id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function payToSeller($sellerId, $sellerPayReason, $entityId);
    
    /**
     * Interface for assign product(s) to the seller.
     *
     * @api
     *
     * @param int $sellerId Seller id
     * @param string $productIds Product Ids
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function assignProduct($sellerId, $productIds);
   
    /**
     * Interface for assign product(s) to the seller.
     *
     * @api
     *
     * @param int $sellerId Seller Id
     * @param string $productIds Product Ids
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function unassignProduct($sellerId, $productIds);
}
