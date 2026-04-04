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
namespace Bss\ProductStockAlert\Block\Customer;

class ListView extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\ProductStockAlert\Model\ResourceModel\Stock\CollectionFactory
     */
    protected $modelcollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $imageHelperFactory;

    /**
     * ListView constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bss\ProductStockAlert\Helper\Data $helper
     * @param \Magento\Framework\Registry $registry
     * @param \Bss\ProductStockAlert\Model\ResourceModel\Stock\CollectionFactory $modelcollectionFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Helper\ImageFactory $imageHelperFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Bss\ProductStockAlert\Helper\Data $helper,
        \Magento\Framework\Registry $registry,
        \Bss\ProductStockAlert\Model\ResourceModel\Stock\CollectionFactory $modelcollectionFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->registry = $registry;
        $this->helper = $helper;
        $this->modelcollectionFactory = $modelcollectionFactory;
        $this->productRepository = $productRepository;
        $this->imageHelperFactory = $imageHelperFactory;
    }

    /**
     * Preparing global layout
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('My Product Subscription'));
        if ($this->getItems()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'magecomp.category.pager'
            )->setAvailableLimit([5=>5,10=>10,15=>15])->setShowPerPage(true)->setCollection(
                $this->getItems()
            );
            $this->setChild('pager', $pager);
            $this->getItems()->load();
        }
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItems()
    {
        //get values of current page
        $page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        //get values of current limit
        $pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 5;

        $newsCollection = $this->modelcollectionFactory->create();
        $newsCollection->addFieldToFilter('customer_id', ['eq' => $this->helper->getCustomerId()]);
        $newsCollection->addFieldToFilter('website_id', ['eq' => $this->helper->getWebsiteId()]);
        $newsCollection->setPageSize($pageSize);
        $newsCollection->setCurPage($page);

        return $newsCollection;
    }

    /**
     * @param string $productId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductUrl($productId)
    {
        $product = $this->productRepository->getById($productId);
        return $product->getProductUrl();
    }

    /**
     * @param string $productId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductName($productId)
    {
        $product = $this->productRepository->getById($productId);
        return $product->getName();
    }

    /**
     * @param string $productId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductImageUrl($productId)
    {
        $product = $this->productRepository->getById($productId);
        $imageUrl = $this->imageHelperFactory->create()
            ->init($product, 'product_thumbnail_image')
            ->resize(135, 135)->getUrl();
        return $imageUrl;
    }

    /**
     * @param string $productId
     * @return string
     */
    public function getUnsubUrl($productId)
    {
        return $this->getUrl(
            'productstockalert/unsubscribe/stock',
            [
                'product_id' => $productId,
                'backurl' => '1',
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
            ]
        );
    }

    /**
     * @param string $productId
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStatusStock($productId)
    {
        $product = $this->productRepository->getById($productId);
        return $product->getIsSalable() ? "In Stock" : "Out Of Stock";
    }

    /**
     * @return string
     */
    public function getUnsubAllUrl()
    {
        return $this->getUrl(
            'productstockalert/unsubscribe/stockAll',
            [
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl()
            ]
        );
    }

    /**
     * @return mixed
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getStopNotifyText()
    {
        return $this->helper->getStopButtonText();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getStopAllNotifyText()
    {
        return __('Stop All Notification');
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getButtonColor()
    {
        return $this->helper->getButtonColor();
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getButtonTextColor()
    {
        return $this->helper->getButtonTextColor();
    }
}
