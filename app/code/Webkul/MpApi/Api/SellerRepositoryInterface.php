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

/**
 * Seller CRUD interface.
 */
interface SellerRepositoryInterface
{
    /**
     * Create Seller.
     *
     * @api
     * @param \Webkul\Marketplace\Api\Data\SellerInterface $seller
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     * @throws \Magento\Framework\Exception\InputException If bad input is provided
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     * If the provided email is already used
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Webkul\Marketplace\Api\Data\SellerInterface $seller);

    /**
     * Retrieve Seller.
     *
     * @api
     * @param int $id
     * @return \Webkul\Marketplace\Api\Data\SellerInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * If seller with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve Seller Data.
     *
     * @api
     * @param int $id
     * @param int $storeId
     * @return Magento\Framework\Api\SearchResults
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * If seller with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDataBySellerId($id, $storeId = 0);

    /**
     * Retrieve sellers which match a specified criteria.
     *
     * @api
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Customer\Api\Data\CustomerSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Seller.
     *
     * @api
     * @param \Webkul\Marketplace\Api\Data\SellerInterface $seller
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Webkul\Marketplace\Api\Data\SellerInterface $seller);

    /**
     * Delete Seller by ID.
     *
     * @api
     * @param int $sellerId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($sellerId);
}
