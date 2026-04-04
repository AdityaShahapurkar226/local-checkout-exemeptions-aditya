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

interface SessionConfigInterface
{
    /**
     * Const
     */
    const PATH = 'path';
    const DOMAIN = 'domain';
    const LIFE_TIME = 'life_time';
    const SECURE = 'secure';
    const HTTP_ONLY = 'http_only';

    /**
     * Init session data info
     * @return $this
     */
    public function init();

    /**
     * @return string
     */
    public function getCookiePath();

    /**
     * @return string
     */
    public function getCookieDomain();

    /**
     * @return string
     */
    public function getCookieLifetime();

    /**
     * @return bool
     */
    public function getCookieSecure();

    /**
     * @return bool
     */
    public function getCookieHttpOnly();

    /**
     * @return $this
     */
    public function setCookiePath($path);

    /**
     * @return $this
     */
    public function setCookieDomain($domain);

    /**
     * @return $this
     */
    public function setCookieLifetime($lifetime);

    /**
     * @return $this
     */
    public function setCookieSecure($secure);

    /**
     * @return $this
     */
    public function setCookieHttpOnly($httponly);
}
