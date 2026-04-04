<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Model;

class Email extends \Magento\Framework\Model\AbstractModel
{
    const XML_PATH_EMAIL_STOCK_TEMPLATE = 'bss_productstockalert/productstockalert/email_stock_template';

    const XML_PATH_EMAIL_IDENTITY = 'bss_productstockalert/productstockalert/email_identity';

    /**
     * Type
     *
     * @var string
     */
    protected $_type = 'stock';

    /**
     * Website Model
     *
     * @var \Magento\Store\Model\Website
     */
    protected $_website;

    /**
     * @var \Magento\Store\Model\Store
     */
    protected $_store;

    /**
     * Customer Name
     *
     * @var string
     */
    protected $_customerName;

    /**
     * Customer Email
     *
     * @var string
     */
    protected $_customerEmail;

    /**
     * Product collection which of back in stock
     *
     * @var array
     */
    protected $_stockProducts = [];

    /**
     * Stock block
     *
     * @var \Bss\ProductStockAlert\Block\Email\Stock
     */
    protected $_stockBlock;

    /**
     * Product alert data
     *
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $_productAlertData = null;

    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $_appEmulation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var ResourceModel\Stock
     */
    protected $stock;

    /**
     * @var \Bss\ProductStockAlert\Helper\AlertData
     */
    protected $alertData;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Bss\ProductStockAlert\Helper\Data $productAlertData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Helper\View $customerHelper
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Bss\ProductStockAlert\Helper\AlertData $alertData,
        \Magento\Framework\Model\Context $context,
        \Bss\ProductStockAlert\Model\ResourceModel\Stock $stock,
        \Magento\Framework\Registry $registry,
        \Bss\ProductStockAlert\Helper\Data $productAlertData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->alertData = $alertData;
        $this->stock = $stock;
        $this->_productAlertData = $productAlertData;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->_appEmulation = $appEmulation;
        $this->_transportBuilder = $transportBuilder;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Set model type
     *
     * @param string $type
     * @return void
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * Retrieve model type
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Set website model
     *
     * @param \Magento\Store\Model\Website $website
     * @return $this
     */
    public function setWebsite(\Magento\Store\Model\Website $website)
    {
        $this->_website = $website;
        return $this;
    }

    /**
     * Set website model
     *
     * @param \Magento\Store\Model\Store|int $store
     * @return $this
     */
    public function setStore($store)
    {
        if ($store instanceof \Magento\Store\Model\Store) {
            $this->_store = $store;
        } else {
            $this->_store = $this->_storeManager->getStore($store);
        }
        return $this;
    }

    /**
     * Set website id
     *
     * @param int $websiteId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setWebsiteId($websiteId)
    {
        $this->_website = $this->_storeManager->getWebsite($websiteId);
        return $this;
    }

    /**
     * @param $storeId
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setStoreId($storeId)
    {
        $this->_store = $this->_storeManager->getStore($storeId);
        return $this;
    }

    /**
     * Set customer name
     *
     * @param string $customerName
     * @return $this
     */
    public function setCustomerName($customerName)
    {
        $this->_customerName = $customerName;
        return $this;
    }

    /**
     * Set customer Email
     *
     * @param string $customerName
     * @return $this
     */
    public function setCustomerEmail($customerEmail)
    {
        $this->_customerEmail = $customerEmail;
        return $this;
    }

    /**
     * Clean data
     *
     * @return $this
     */
    public function clean()
    {
        $this->_customer = null;
        $this->_customerEmail = null;
        $this->_stockProducts = [];

        return $this;
    }

    /**
     * Add product (back in stock) to collection
     *
     * @param array $productData
     * @return $this
     */
    public function addStockProduct($productData)
    {
        if (isset($productData['product_id'])) {
            $this->_stockProducts[$productData['product_id']] = $productData;
        }
        return $this;
    }

    /**
     * @return \Bss\ProductStockAlert\Block\Email\Stock|\Magento\Framework\View\Element\AbstractBlock
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getStockBlock()
    {
        if ($this->_stockBlock === null) {
            $this->_stockBlock = $this->alertData->createBlock(\Bss\ProductStockAlert\Block\Email\Stock::class);
        }
        return $this->_stockBlock;
    }

    /**
     * Send customer email
     *
     * @return bool
     * @throws \Magento\Framework\Exception\MailException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function send()
    {
        if ($this->_store === null || $this->_customerName === null) {
            return false;
        }
        if ($this->_type == 'stock' && count($this->_stockProducts) == 0) {
            return false;
        }

        $storeId = $this->_store->getId();

        if ($this->_type == 'stock' &&
            !$this->_scopeConfig->getValue(
                self::XML_PATH_EMAIL_STOCK_TEMPLATE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )
        ) {
            return false;
        }

        if ($this->_type != 'stock') {
            return false;
        }

        $block = $this->_getStockBlock();
        $this->stock->executeReset($block->setStore($this->_store));

        $templateId = $this->_scopeConfig->getValue(
            self::XML_PATH_EMAIL_STOCK_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $transport = $this->_transportBuilder->setTemplateIdentifier(
            $templateId
        )->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars(
            [
                'store_url' => $this->_store->getBaseUrl(),
                'customerName' => $this->_customerName,
                'product_data' => $this->_stockProducts,
                'store' => $this->_store,
            ]
        )->setFrom(
            $this->_scopeConfig->getValue(
                self::XML_PATH_EMAIL_IDENTITY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )
        )->addTo(
            $this->_customerEmail,
            $this->_customerName
        )->getTransport();

        $transport->sendMessage();

        return true;
    }
}
