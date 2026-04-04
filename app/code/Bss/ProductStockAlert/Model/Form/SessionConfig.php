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

use Magento\Framework\DataObject;
use Magento\Framework\Session\SessionManagerInterface;

class SessionConfig extends DataObject implements SessionConfigInterface
{
    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * SessionConfig constructor.
     * @param SessionManagerInterface $sessionManager
     * @param array $data
     */
    public function __construct(
        SessionManagerInterface $sessionManager,
        array $data = []
    ) {
        $this->sessionManager = $sessionManager;
        parent::__construct($data);
    }

    /**
     * @return $this
     */
    public function init()
    {
        $this->setCookiePath('/'); // cross area
        $this->setCookieDomain($this->sessionManager->getCookieDomain());
        $this->setCookieLifetime(86400); // 1day
        $this->setCookieSecure(false);
        $this->setCookieHttpOnly(false);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCookiePath()
    {
        return $this->getData(self::PATH);
    }

    /**
     * @inheritDoc
     */
    public function getCookieDomain()
    {
        return $this->getData(self::DOMAIN);
    }

    /**
     * @inheritDoc
     */
    public function getCookieLifetime()
    {
        return $this->getData(self::LIFE_TIME);
    }

    /**
     * @inheritDoc
     */
    public function getCookieSecure()
    {
        return $this->getData(self::SECURE);
    }

    /**
     * @inheritDoc
     */
    public function getCookieHttpOnly()
    {
        return $this->getData(self::HTTP_ONLY);
    }

    /**
     * @inheritDoc
     */
    public function setCookiePath($path)
    {
        return $this->setData(self::PATH, $path);
    }

    /**
     * @inheritDoc
     */
    public function setCookieDomain($domain)
    {
        return $this->setData(self::DOMAIN, $domain);
    }

    /**
     * @inheritDoc
     */
    public function setCookieLifetime($lifetime)
    {
        return $this->setData(self::LIFE_TIME, $lifetime);
    }

    /**
     * @inheritDoc
     */
    public function setCookieSecure($secure)
    {
        return $this->setData(self::SECURE, $secure);
    }

    /**
     * @inheritDoc
     */
    public function setCookieHttpOnly($httponly)
    {
        return $this->setData(self::HTTP_ONLY, $httponly);
    }
}
