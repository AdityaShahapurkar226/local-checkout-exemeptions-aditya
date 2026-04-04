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
namespace Webkul\SellerSubAccount\Api;

/**
 * SubAccount CRUD interface.
 */
interface SubAccountRepositoryInterface
{
    /**
     * Retrieve SubAccount Data By Id.
     *
     * @api
     * @param string $entityId
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($entityId);

    /**
     * Retrieve SubAccount Data By seller id.
     *
     * @api
     * @param int $sellerId
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * If Data with the specified Seller Id does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBySellerId($sellerId);

    /**
     * Retrieve Active SubAccount Data By customer id.
     *
     * @api
     * @param int $customerId
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * If Data with the specified Seller Id does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getActiveByCustomerId($customerId);

    /**
     * Retrieve SubAccount Data By customer id.
     *
     * @api
     * @param int $customerId
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * If Data with the specified Seller Id does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByCustomerId($customerId);

    /**
     * Retrieve SubAccount Data By Seller id and subAccount name.
     *
     * @api
     * @param int $sellerId
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getActiveAccountsBySellerId($sellerId);

    /**
     * Retrieve SubAccount Collection.
     *
     * @api
     * @return \Webkul\SellerSubAccount\Api\Data\SubAccountInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList();

    /**
     * Delete SubAccount.
     *
     * @api
     * @param \Webkul\SellerSubAccount\Api\Data\SubAccountInterface $subAccount
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Webkul\SellerSubAccount\Api\Data\SubAccountInterface $subAccount);

    /**
     * Delete SubAccount by ID.
     *
     * @api
     * @param int $entityId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($entityId);

    /**
     * Delete SubAccount by Seller ID.
     *
     * @api
     * @param int $sellerId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteBySellerId($sellerId);
}
