<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplaceBaseShipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplaceBaseShipping\Model;

use Webkul\MarketplaceBaseShipping\Api\Data;
use Webkul\MarketplaceBaseShipping\Api\ShippingSettingRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MarketplaceBaseShipping\Model\ResourceModel\ShippingSetting as ResourceShippingSetting;
use Webkul\MarketplaceBaseShipping\Model\ResourceModel\ShippingSetting\CollectionFactory
as ShippingSettingCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ShippingSettingRepository provides data
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingSettingRepository implements ShippingSettingRepositoryInterface
{
    /**
     * @var ResourceBlock
     */
    protected $resource;

    /**
     * @var BlockFactory
     */
    protected $timeSlotConfigFactory;

    /**
     * @var BlockCollectionFactory
     */
    protected $shippingSettingCollectionFactory;

    /**
     * @var Data\BlockSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterfaceFactory
     */
    protected $dataCustomerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceShippingSetting $resource
     * @param ShippingSettingFactory $shippingSettingFactory
     * @param \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterfaceFactory $dataCustomerFactory
     * @param ShippingSettingCollectionFactory $shippingSettingCollectionFactory
     * @param Data\ShippingSettingSearchResultInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceShippingSetting $resource,
        ShippingSettingFactory $shippingSettingFactory,
        \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterfaceFactory $dataCustomerFactory,
        ShippingSettingCollectionFactory $shippingSettingCollectionFactory,
        Data\ShippingSettingSearchResultInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->shippingSettingFactory = $shippingSettingFactory;
        $this->shippingSettingCollectionFactory = $shippingSettingCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataCustomerFactory = $dataCustomerFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save Customer data
     *
     * @param \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface $shippingSetting
     * @return PreorderComplete
     * @throws CouldNotSaveException
     */
    public function save(Data\ShippingSettingInterface $shippingSetting)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $shippingSetting->setStoreId($storeId);
        try {
            $this->resource->save($shippingSetting);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $shippingSetting;
    }

    /**
     * Load customer data by given chat unique id Identity
     *
     * @param string $id
     * @return ShippingSetting
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $shippingSetting = $this->shippingSettingFactory->create();
        $this->resource->load($shippingSetting, $id);
        return $shippingSetting;
    }

    /**
     * Load setting by customer id Identity
     *
     * @param string $sellerId
     * @return ShippingSetting
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBySellerId($sellerId)
    {
        $shippingSetting = $this->shippingSettingFactory->create()
            ->getCollection();
        $shippingSetting->addFieldToFilter('seller_id', ['eq' => $sellerId]);
        $shippingSetting->setPageSize(1);
        
        $entityId = $shippingSetting->getFirstItem()->getId();
        return $this->getById($entityId);
    }
    /**
     * Delete PreorderComplete
     *
     * @param \Webkul\MarketplaceBaseShipping\Api\Data\ShippingSettingInterface $shippingSetting
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Data\ShippingSettingInterface $shippingSetting)
    {
        try {
            $this->resource->delete($shippingSetting);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete PreorderComplete by given Block Identity
     *
     * @param string $id
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
