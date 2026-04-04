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
namespace Bss\ProductStockAlert\Model\Form;

class FormKey
{
    const FORM_KEY = '_bss_form_key';

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * Cookie Manager
     *
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var SessionConfigInterface
     */
    protected $sessionConfig;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * FormKey constructor.
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param SessionConfigInterface $sessionConfig
     * @param \Magento\Framework\Math\Random $mathRandom
     */
    public function __construct(
        \Magento\Framework\Escaper $escaper,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        SessionConfigInterface $sessionConfig,
        \Magento\Framework\Math\Random $mathRandom
    ) {
        $this->escaper = $escaper;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->cookieManager = $cookieManager;
        $this->sessionConfig = $sessionConfig;
        $this->mathRandom = $mathRandom;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFormKey()
    {
        $formKey = $this->cookieManager->getCookie(self::FORM_KEY);
        if (!$this->isPresent()) {
            $formKey = $this->mathRandom->getRandomString(16);
            $this->setData($formKey);
        }
        return $this->escaper->escapeJs($formKey);
    }

    /**
     * Determine if the form key is present in the session
     *
     * @return bool
     */
    public function isPresent()
    {
        return (bool) $this->cookieManager->getCookie(self::FORM_KEY);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setData($value)
    {
        return $this->set($value);
    }

    /**
     * @return $this
     */
    public function clearData()
    {
        $this->clear();
        return $this;
    }

    /**
     * @return $this
     */
    public function renewData()
    {
        $this->renew();
        return $this;
    }

    /**
     * @param $cookieValue
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    private function set($cookieValue)
    {
        if ($cookieValue) {
            $this->sessionConfig->init();
            $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
            $metadata->setPath($this->sessionConfig->getCookiePath());
            $metadata->setDomain($this->sessionConfig->getCookieDomain());
            $metadata->setDuration($this->sessionConfig->getCookieLifetime());
            $metadata->setSecure($this->sessionConfig->getCookieSecure());
            $metadata->setHttpOnly($this->sessionConfig->getCookieHttpOnly());

            $this->cookieManager->setPublicCookie(
                self::FORM_KEY,
                $cookieValue,
                $metadata
            );
        }
        return $this;
    }

    /**
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    private function clear()
    {
        $this->sessionConfig->init();
        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata();
        $metadata->setPath($this->sessionConfig->getCookiePath());
        $metadata->setDomain($this->sessionConfig->getCookieDomain());
        $metadata->setDuration($this->sessionConfig->getCookieLifetime());
        $metadata->setSecure($this->sessionConfig->getCookieSecure());
        $metadata->setHttpOnly($this->sessionConfig->getCookieHttpOnly());
        $this->cookieManager->deleteCookie(self::FORM_KEY, $metadata);
    }

    /**
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    private function renew()
    {
        $this->clear();
        $this->setData($this->mathRandom->getRandomString(16));
    }
}
