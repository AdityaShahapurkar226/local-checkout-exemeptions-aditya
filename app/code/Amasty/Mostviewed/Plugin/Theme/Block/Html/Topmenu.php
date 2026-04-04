<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Plugin\Theme\Block\Html;

use Amasty\Mostviewed\Helper\Config;
use Amasty\Mostviewed\Model\OptionSource\TopMenuLink;
use Magento\Framework\Data\Tree\Node;

class Topmenu
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    private $pageFactory;

    public function __construct(
        \Magento\Framework\UrlInterface $url,
        Config $config,
        \Magento\Cms\Model\PageFactory $pageFactory
    ) {
        $this->url = $url;
        $this->config = $config;
        $this->pageFactory = $pageFactory;
    }

    /**
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     * @return array
     */
    public function beforeGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    ) {
        if ($this->isEnabled() && $this->getBundlesUrl()) {
            $node = new Node(
                $this->_getNodeAsArray(),
                'id',
                $subject->getMenu()->getTree(),
                $subject->getMenu()
            );
            $subject->getMenu()->addChild($node);
        }

        return [$outermostClass, $childrenWrapClass, $limit];
    }

    /**
     * @return array
     */
    protected function _getNodeAsArray()
    {
        $url = $this->getBundlesUrl();
        return [
            'name' => $this->getLabel(),
            'id' => 'amasty_mostviwed_bundle_packs',
            'url' => $url,
            'has_active' => false,
            'is_active' => $url == $this->url->getCurrentUrl()
        ];
    }

    /**
     * @return string
     */
    protected function getLabel()
    {
        return (string)$this->config->getModuleConfig('bundle_packs/menu_item_label');
    }

    /**
     * @return bool
     */
    protected function isEnabled()
    {
        return $this->getPosition() == $this->getTopMenuEnabled();
    }

    /**
     * @return int
     */
    protected function getTopMenuEnabled()
    {
        return (int) $this->config->getModuleConfig('bundle_packs/top_menu_enabled');
    }

    /**
     * @return int
     */
    protected function getPosition()
    {
        return TopMenuLink::DISPLAY_FIRST;
    }

    /**
     * @return string
     */
    protected function getBundlesUrl()
    {
        $pageIdentifier = $this->config->getModuleConfig('bundle_packs/cms_page');

        $identifierWithId = explode('|', $pageIdentifier);

        $page = $this->pageFactory->create()->load($identifierWithId[0], 'identifier');
        $url = '';

        if ($page && $page->isActive()) {
            $url = $this->url->getUrl($identifierWithId[0]);
        }

        return $url;
    }
}
