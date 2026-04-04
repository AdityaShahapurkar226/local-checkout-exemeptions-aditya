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
namespace Webkul\SellerSubAccount\Plugin\Block\Product;

use Webkul\SellerSubAccount\Helper\Data as HelperData;
use Magento\Framework\ObjectManagerInterface;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as EntityAttribute;
use Webkul\Marketplace\Model\ResourceModel\Product\Collection;
use Webkul\Marketplace\Model\Product as MarketplaceProduct;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Productlist
{
    /**
     * @var HelperData
     */
    public $_helper;

    /**
     * @var MarketplaceHelper
     */
    public $_marketplaceHelper;

    /**
     * @var EntityAttribute
     */
    public $_entityAttribute;

    /**
     * @var Collection
     */
    public $_collection;

    /**
     * @var MarketplaceProduct
     */
    public $_marketplaceProduct;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    public $_productlists;

    /**
     * @param HelperData $helper
     * @param MarketplaceHelper $marketplaceHelper
     * @param EntityAttribute $entityAttribute
     * @param Collection $collection
     * @param MarketplaceProduct $marketplaceProduct
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param CollectionFactory $productCollectionFactory
     */
    public function __construct(
        HelperData $helper,
        MarketplaceHelper $marketplaceHelper,
        EntityAttribute $entityAttribute,
        Collection $collection,
        MarketplaceProduct $marketplaceProduct,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        CollectionFactory $productCollectionFactory
    ) {
        $this->_helper = $helper;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_entityAttribute = $entityAttribute;
        $this->_collection = $collection;
        $this->_marketplaceProduct = $marketplaceProduct;
        $this->localeDate = $localeDate;
        $this->_productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Get All Products.
     *
     * @param \Webkul\Marketplace\Block\Product\Productlist $block
     * @param \Closure $proceed
     *
     * @return bool|\Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function aroundGetAllProducts(
        \Webkul\Marketplace\Block\Product\Productlist $block,
        \Closure $proceed
    ) {
        $subAccount = $this->_helper->getCurrentSubAccount();
        if (!$subAccount->getId()) {
            $result = $proceed();
            return $result;
        }
        $storeId = $this->_marketplaceHelper->getCurrentStoreId();
        $websiteId = $this->_marketplaceHelper->getWebsiteId();
        if (!($customerId = $this->_helper->getSubAccountSellerId())) {
            return false;
        }
        if (!$this->_productlists) {
            $paramData = $block->getRequest()->getParams();
            $filter = '';
            $filterStatus = '';
            $filterDateFrom = '';
            $filterDateTo = '';
            $from = null;
            $to = null;

            if (isset($paramData['s'])) {
                $filter = $paramData['s'] != '' ? $paramData['s'] : '';
            }
            if (isset($paramData['status'])) {
                $filterStatus = $paramData['status'] != '' ? $paramData['status'] : '';
            }
            if (isset($paramData['from_date'])) {
                $filterDateFrom = $paramData['from_date'] != '' ? $paramData['from_date'] : '';
            }
            if (isset($paramData['to_date'])) {
                $filterDateTo = $paramData['to_date'] != '' ? $paramData['to_date'] : '';
            }
            if ($filterDateTo) {
                $todate = date_create($filterDateTo);
                $to = date_format($todate, 'Y-m-d 23:59:59');
            }
            if (!$to) {
                $to = date('Y-m-d 23:59:59');
            }
            if ($filterDateFrom) {
                $fromdate = date_create($filterDateFrom);
                $from = date_format($fromdate, 'Y-m-d H:i:s');
            }

            $proAttId = $this->_entityAttribute->getIdByCode('catalog_product', 'name');
            $proStatusAttId = $this->_entityAttribute->getIdByCode(
                'catalog_product',
                'status'
            );

            $catalogProductEntity = $this->_collection->getTable('catalog_product_entity');

            $catalogProductEntityVarchar = $this->_collection->getTable(
                'catalog_product_entity_varchar'
            );

            $catalogProductEntityInt = $this->_collection->getTable(
                'catalog_product_entity_int'
            );

            /* Get Seller Product Collection for current Store Id */

            $storeCollection = $this->_marketplaceProduct
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $customerId
            )->addFieldToSelect(
                ['mageproduct_id']
            );

            $storeCollection->getSelect()->join(
                $catalogProductEntityVarchar.' as cpev',
                'main_table.mageproduct_id = cpev.entity_id'
            )->where(
                'cpev.store_id = '.$storeId.' AND
                cpev.value like "%'.$filter.'%" AND
                cpev.attribute_id = '.$proAttId
            );

            $storeCollection->getSelect()->join(
                $catalogProductEntityInt.' as cpei',
                'main_table.mageproduct_id = cpei.entity_id'
            )->where(
                'cpei.store_id = '.$storeId.' AND
                cpei.attribute_id = '.$proStatusAttId
            );

            if ($filterStatus) {
                $storeCollection->getSelect()->where(
                    'cpei.value = '.$filterStatus
                );
            }

            $storeCollection->getSelect()->join(
                $catalogProductEntity.' as cpe',
                'main_table.mageproduct_id = cpe.entity_id'
            );

            if ($from && $to) {
                $storeCollection->getSelect()->where(
                    "cpe.created_at BETWEEN '".$from."' AND '".$to."'"
                );
            }

            $storeCollection->getSelect()->group('mageproduct_id');

            $storeProductIDs = $storeCollection->getAllIds();

            /* Get Seller Product Collection for 0 Store Id */

            $adminStoreCollection = $this->_marketplaceProduct->getCollection();

            if (count($storeCollection->getAllIds())) {
                $adminStoreCollection->addFieldToFilter(
                    'mageproduct_id',
                    ['nin' => $storeCollection->getAllIds()]
                );
            }
            $adminStoreCollection->addFieldToFilter(
                'seller_id',
                $customerId
            )->addFieldToSelect(
                ['mageproduct_id']
            );

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntityVarchar.' as acpev',
                'main_table.mageproduct_id = acpev.entity_id'
            )->where(
                'acpev.store_id = 0 AND
                acpev.value like "%'.$filter.'%" AND
                acpev.attribute_id = '.$proAttId
            );

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntityInt.' as acpei',
                'main_table.mageproduct_id = acpei.entity_id'
            )->where(
                'acpei.store_id = 0 AND
                acpei.attribute_id = '.$proStatusAttId
            );

            if ($filterStatus) {
                $adminStoreCollection->getSelect()->where(
                    'acpei.value = '.$filterStatus
                );
            }

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntity.' as acpe',
                'main_table.mageproduct_id = acpe.entity_id'
            );
            if ($from && $to) {
                $adminStoreCollection->getSelect()->where(
                    "acpe.created_at BETWEEN '".$from."' AND '".$to."'"
                );
            }

            $adminStoreCollection->getSelect()->group('mageproduct_id');

            $adminProductIDs = $adminStoreCollection->getAllIds();

            $productIDs = array_merge($storeProductIDs, $adminProductIDs);

            $collection = $this->_marketplaceProduct
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $customerId
            )
            ->addFieldToFilter(
                'mageproduct_id',
                ['in' => $productIDs]
            );
           
            $today = $this->localeDate->date(strtotime('-2 day'))->format('Y-m-d H:i:s');
            if (!empty($productIDs)) {
                $collection =  $this->_productCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', ['in' => $productIDs])
                ->addAttributeToFilter('deal_to_date', ['gteq' => $today])
                ->addAttributeToFilter('deal_status', ['neq' => 0])
                ->addFieldToFilter('type_id', ['nin'=> ['grouped','configurable']])
                ->setOrder('entity_id', 'desc');
            }
            
            $this->_productlists = $collection;
        }

        return $this->_productlists;
    }
}
