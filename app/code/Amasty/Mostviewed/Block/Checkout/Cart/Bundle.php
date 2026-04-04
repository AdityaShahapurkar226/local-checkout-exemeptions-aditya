<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Block\Checkout\Cart;

use Amasty\Mostviewed\Api\Data\PackInterface;
use Magento\Framework\View\Element\Template;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Bundle extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Mostviewed::bundle/cart_bundle.phtml';

    /**
     * @var \Amasty\Mostviewed\Helper\Config
     */
    private $config;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    /**
     * @var \Amasty\Mostviewed\Api\PackRepositoryInterface
     */
    private $packRepository;

    /**
     * @var null|array
     */
    private $productsInCart = null;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        Template\Context $context,
        \Amasty\Mostviewed\Helper\Config $config,
        \Magento\Checkout\Model\Session $session,
        \Amasty\Mostviewed\Api\PackRepositoryInterface $packRepository,
        ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->session = $session;
        $this->packRepository = $packRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if ($this->isEnabled()) {
            return parent::toHtml();
        }

        return '';
    }

    /**
     * @param PackInterface $bundle
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function renderBundle(PackInterface $bundle)
    {
        $layout = $this->getLayout();
        $block = $layout->getBlock('amrelated.bundle.page.wrapper');
        if (!$block) {
            $block = $layout->createBlock(
                \Amasty\Mostviewed\Block\Product\BundlePackWrapper::class,
                'amrelated.bundle.page.wrapper',
                [ 'data' => [] ]
            );
        }

        $html = '';
        try {
            $product = $this->getProduct($bundle);
            if ($product) {
                $html = $block->setBundles([$bundle])
                    ->setProduct($product)
                    ->toHtml();
            }
        } catch (\Exception $ex) {
            $html = '';
        }

        return $html;
    }

    /**
     * @param PackInterface $bundle
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    private function getProduct(PackInterface $bundle)
    {
        if ($bundle->getValidateFlag()) {
            $ids = array_intersect($bundle->getParentIds(), $this->getProductsInCart());
        } else {
            $ids = array_diff($bundle->getParentIds(), $this->getProductsInCart());
        }

        if (!$ids) {
            $ids = $bundle->getParentIds();
        }

        $product = null;
        while (count($ids) && !$product) {
            $productId = array_shift($ids);
            $product = $this->productRepository->getById(
                $productId,
                false,
                $this->_storeManager->getStore()->getId()
            );

            $product = $product->isSaleable() ? $product : null;
        }

        return $product;
    }

    /**
     * @return bool
     */
    private function isEnabled()
    {
        return $this->config->isBlockInCartEnabled() && $this->getProductsInCart();
    }

    /**
     * @return array
     */
    private function getProductsInCart()
    {
        if ($this->productsInCart === null) {
            $this->productsInCart = [];
            foreach ($this->session->getQuote()->getAllItems() as $quoteItem) {
                $this->productsInCart[] = $quoteItem->getProductId();
            }
        }

        return $this->productsInCart;
    }

    /**
     * @return PackInterface
     */
    public function getBundle()
    {
        $data = [];

        $packs = $this->packRepository->getPacksByParentProductsAndStore(
            $this->getProductsInCart(),
            $this->_storeManager->getStore()->getId()
        );
        $data = $this->validatePacks($data, $packs, true);

        $packs = $this->packRepository->getPacksByChildProductsAndStore(
            $this->getProductsInCart(),
            $this->_storeManager->getStore()->getId()
        );
        $data = $this->validatePacks($data, $packs, false);

        if ($data) {
            $data = $data[array_rand($data)];//get random pack
        }

        return $data;
    }

    /**
     * @param array $packs
     * @param array $data
     * @param bool $isParent
     *
     * @return array
     */
    private function validatePacks($data, $packs, $isParent)
    {
        if ($packs) {
            /** @var PackInterface $pack */
            foreach ($packs as $pack) {
                $productsToCheck = $isParent ? explode(',', $pack->getProductIds()) : $pack->getParentIds();
                $missedProducts = array_diff(
                    $productsToCheck,
                    $this->getProductsInCart()
                );
                if (!empty($missedProducts)) {
                    $pack->setValidateFlag($isParent);
                    $data[] = $pack;
                }
            }
        }

        return $data;
    }
}
