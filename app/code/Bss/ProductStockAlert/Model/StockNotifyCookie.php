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
 * @category  BSS
 * @package   Bss_ProductStockAlert
 * @author    Extension Team
 * @copyright Copyright (c) 2016-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\ProductStockAlert\Model;

use Bss\ProductStockAlert\Helper\Data;
use Bss\ProductStockAlert\Model\ResourceModel\Stock\CollectionFactory;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

class StockNotifyCookie
{
    /**
     * Default COOKIE DURATION
     */
    public const COOKIE_DURATION = 86400; // cookie die after 1 day

    /**
     * Name cookie save all product ids have btn cancel
     */
    public const COOKIE_ALl_PRODUCT_ID = 'productIdCookie';

    /**
     * Cookie name eg: stockNotifyCookie-1
     */
    public const COOKIE_NAME_BEFORE = "stockNotifyCookie-";

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var \Bss\ProductStockAlert\Model\ResourceModel\Stock\CollectionFactory
     */
    protected $modelcollectionFactory;

    /**
     * @var \Bss\ProductStockAlert\Helper\Data
     */
    protected $helper;

    /**
     * @var StockBtn
     */
    protected $stockBtn;

    /**
     * Constructor.
     *
     * @param CollectionFactory $modelcollectionFactory
     * @param Data $helper
     * @param StockBtn $stockBtn
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        \Bss\ProductStockAlert\Model\ResourceModel\Stock\CollectionFactory $modelcollectionFactory,
        \Bss\ProductStockAlert\Helper\Data $helper,
        \Bss\ProductStockAlert\Model\StockBtn $stockBtn,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager
    ) {
        $this->modelcollectionFactory = $modelcollectionFactory;
        $this->helper = $helper;
        $this->stockBtn = $stockBtn;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Get cookie value by name.
     *
     * @param string $cookieName
     * @return string|null
     */
    public function get($cookieName)
    {
        return $this->cookieManager->getCookie($cookieName);
    }

    /**
     * Set value cookie.
     *
     * @param string $cookieName
     * @param string|mixed $value
     * @return void
     * @throws \Exception
     */
    public function set($cookieName, $value)
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setDuration(self::COOKIE_DURATION)
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain());
        try {
            $this->cookieManager->setPublicCookie(
                $cookieName,
                $value,
                $metadata
            );
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Delete cookie.
     *
     * @param string $cookieName
     * @return void
     * @throws \Exception
     */
    public function delete($cookieName)
    {
        $metadata = $this->cookieMetadataFactory
            ->createPublicCookieMetadata()
            ->setPath($this->sessionManager->getCookiePath())
            ->setDomain($this->sessionManager->getCookieDomain());
        try {
            $this->cookieManager->deleteCookie($cookieName, $metadata);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Get all product stock notify by customer login.
     *
     * @return array
     */
    public function getAllStock()
    {
        if (!$this->helper->getCustomerId()) {
            return [];
        }

        try {
            $collection = $this->modelcollectionFactory->create();
            $collection->addFieldToFilter('customer_id', ['eq' => $this->helper->getCustomerId()]);
            $collection->addFieldToFilter('website_id', ['eq' => $this->helper->getWebsiteId()]);

            return $collection->getData() ?: [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Set button to Cookie.
     *
     * @param int $productId
     * @param int|null $parentProductId
     * @return void
     * @throws \Exception
     */
    public function setBtnCookie($productId, $parentProductId)
    {
        //add btn cookie
        $cookieName = self::COOKIE_NAME_BEFORE . $productId;
        $btn = $this->stockBtn->getBtnAfterAddNotify($productId, $parentProductId);
        $this->set($cookieName, $btn);

        //check & add cookie name in list product ids cookie
        $productIds = $this->get(self::COOKIE_ALl_PRODUCT_ID);
        if ($productIds) {
            $dataIds = explode(",", $productIds);
            if (!in_array($cookieName, $dataIds)) {
                $productIdCookie = $productIds . "," . $cookieName;
                $this->set(self::COOKIE_ALl_PRODUCT_ID, $productIdCookie);
            }
        } else {
            $productIdCookie = "0" . "," . $cookieName;
            $this->set(self::COOKIE_ALl_PRODUCT_ID, $productIdCookie);
        }
    }

    /**
     * Delete all btn cookie.
     *
     * @return void
     * @throws \Exception
     */
    public function deleteAll()
    {
        $data = $this->get(self::COOKIE_ALl_PRODUCT_ID) ?
            explode(",", $this->get(self::COOKIE_ALl_PRODUCT_ID)) : [];
        foreach ($data as $cookieName) {
            if ($cookieName) {
                $this->delete($cookieName);
            }
        }

        $this->delete(StockNotifyCookie::COOKIE_ALl_PRODUCT_ID);
    }
}
