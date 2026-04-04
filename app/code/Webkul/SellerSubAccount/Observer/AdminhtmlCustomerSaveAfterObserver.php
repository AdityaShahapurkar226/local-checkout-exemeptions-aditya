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

namespace Webkul\SellerSubAccount\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Webkul\Marketplace\Model\Product as ProductStatus;

/**
 * Webkul Marketplace AdminhtmlCustomerSaveAfterObserver Observer.
 */
class AdminhtmlCustomerSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    public $_fileUploaderFactory;

    /**
     * @var ObjectManagerInterface
     */
    public $_objectManager;

    /**
     * @var CollectionFactory
     */
    public $_collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $_date;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $_storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    public $_productRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $_messageManager;

    /**
     * @var DirectoryList
     */
    public $_mediaDirectory;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\Collection
     */
    public $_sellerProduct;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    public $_jsonDecoder;
    /**
     * @var \Webkul\Marketplace\Model\SellerFactory
     */
    public $_sellerModel;
    
    /**
     * Constructor
     *
     * @param Filesystem $filesystem
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param CollectionFactory $collectionFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param ProductCollection $sellerProduct
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Webkul\Marketplace\Model\SellerFactory $sellerModel
     */
    public function __construct(
        Filesystem $filesystem,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        ProductCollection $sellerProduct,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Webkul\Marketplace\Model\SellerFactory $sellerModel
    ) {
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_productRepository = $productRepository;
        $this->_objectManager = $objectManager;
        $this->_messageManager = $messageManager;
        $this->_collectionFactory = $collectionFactory;
        $this->_storeManager = $storeManager;
        $this->_date = $date;
        $this->_sellerProduct = $sellerProduct;
        $this->_jsonDecoder = $jsonDecoder;
        $this->_sellerModel = $sellerModel;
    }

    /**
     * Admin customer save after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getCustomer();
        $customerid = $customer->getId();
        $postData = $observer->getRequest()->getPostValue();
        if ($this->isSeller($customerid)) {
            if (isset($postData['subaccountpermission'])) {
                $subaccountpermission = implode(',', $postData['subaccountpermission']);
            } else {
                $subaccountpermission = "";
            }
                $sellerId = $this->getLoadEntityId($customerid);
                $collectionselect = $this->_sellerModel->create()
                ->load($sellerId)
                ->setSubAccountPermission($subaccountpermission);
                $collectionselect->save();
        }
    }

    /**
     * Get Seller's Entity Id
     *
     * @param int $customerid
     */
    public function getLoadEntityId($customerid)
    {
        $model = $this->_sellerModel
        ->create()
        ->getCollection()
        ->addFieldToFilter('seller_id', $customerid)
        ->addFieldToFilter('store_id', 0)
        ->getFirstItem();
        return $model->getEntityId();
    }

    /**
     * IsSeller function returns customer is seller or not
     *
     * @param int $customerid
     * @return boolean
     */
    public function isSeller($customerid)
    {
        $sellerStatus = 0;
        $model = $this->_collectionFactory->create()
        ->addFieldToFilter('seller_id', $customerid)
        ->addFieldToFilter('store_id', 0);
        foreach ($model as $value) {
            $sellerStatus = $value->getIsSeller();
        }
        return $sellerStatus;
    }
}
