<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\View\Page\Config;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\View\Asset\File;
use Magento\Framework\View\Page\Config\Renderer as MagentoRenderer;

class Renderer
{
    public const CACHE_KEY = 'amrelated_should_load_css_file';

    /**
     * @var array
     */
    protected $filesToCheck = ['css/styles-l.css', 'css/styles-m.css'];

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    private $config;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Amasty\Base\Model\MagentoVersion
     */
    private $magentoVersion;

    public function __construct(
        \Magento\Framework\View\Page\Config $config,
        \Amasty\Base\Model\MagentoVersion $magentoVersion,
        \Magento\Framework\App\Request\Http $request,
        CacheInterface $cache
    ) {
        $this->config = $config;
        $this->cache = $cache;
        $this->request = $request;
        $this->magentoVersion = $magentoVersion;
    }

    /**
     * Add our css file if less functionality is missing
     *
     * @param MagentoRenderer $subject
     * @param array $resultGroups
     *
     * @return array
     */
    public function beforeRenderAssets(
        MagentoRenderer $subject,
        $resultGroups = []
    ) {
        $shouldLoad = $this->cache->load(self::CACHE_KEY);
        if ($shouldLoad === false) {
            $shouldLoad = $this->isShouldLoadCss();
            $this->cache->save($shouldLoad, self::CACHE_KEY);
        }

        if ($shouldLoad) {
            // $this->config->addPageAsset('Amasty_Mostviewed::css/source/mkcss/amrelated.css');
        }

        $version = $this->magentoVersion->get();
        $version = str_replace(['-develop', 'dev-'], '', $version);

        if (version_compare($version, '2.3.0', '<')
            && (in_array(
                $this->request->getFullActionName(),
                ['catalog_product_view', 'cms_page_view', 'checkout_cart_index']
            ))
        ) {
            $this->config->addPageAsset('Magento_Swatches::css/swatches.css');
        }

        return [$resultGroups];
    }

    /**
     * @return int
     */
    private function isShouldLoadCss()
    {
        $collection = $this->config->getAssetCollection();
        $found = 0;
        foreach ($collection->getAll() as $item) {
            /** @var File $item */
            if ($item instanceof File
                && in_array($item->getFilePath(), $this->filesToCheck)
            ) {
                $found++;
            }
        }

        return (int)($found < count($this->filesToCheck));
    }
}