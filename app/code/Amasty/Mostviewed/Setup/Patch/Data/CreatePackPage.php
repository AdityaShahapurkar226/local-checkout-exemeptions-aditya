<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Setup\Patch\Data;

use Amasty\Mostviewed\Helper\Config;
use Exception;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\ResourceModel\Page as PageResource;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\FlagManager;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\UrlRewrite\Model\Storage\DbStorage;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

class CreatePackPage implements DataPatchInterface, PatchRevertableInterface
{
    public const IDENTIFIER = 'bundles';
    public const FLAG_CODE_VALUE = 'amasty_mostviewed_pack_page_identifier';

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * @var PageResource
     */
    private $pageResource;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var FlagManager
     */
    private $flagManager;

    public function __construct(
        WriterInterface $configWriter,
        PageFactory $pageFactory,
        PageResource $pageResource,
        ReinitableConfigInterface $reinitableConfig,
        ModuleDataSetupInterface $moduleDataSetup,
        FlagManager $flagManager
    ) {
        $this->pageFactory = $pageFactory;
        $this->configWriter = $configWriter;
        $this->reinitableConfig = $reinitableConfig;
        $this->pageResource = $pageResource;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->flagManager = $flagManager;
    }

    /**
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return CreatePackPage
     */
    public function apply()
    {
        if ($this->isCanApply()) {
            $content = <<<CONTENT
<h2>
    <strong>Searching for special deals? Browse the list below to find the offer you're looking for!</strong>
</h2>
<p></p>
<p>{{widget type="Amasty\Mostviewed\Block\Widget\PackList" columns="3"template="bundle/list.phtml"}}</p>
CONTENT;

            $pageIdentifier = $this->getPageIdentifier();
            $page = $this->pageFactory->create();
            $page->setTitle('All Bundle Packs Page')
                ->setIdentifier($pageIdentifier)
                ->setData('mageworx_hreflang_identifier', 'en-us')
                ->setData('amasty_hreflang_uuid', 'en-us')
                ->setData('mp_exclude_sitemap', '1')
                ->setIsActive(false)
                ->setPageLayout('1column')
                ->setStores([0])
                ->setContent($content)
                ->save();

            $this->configWriter->save(Config::BUNDLE_PAGE_PATH, $pageIdentifier);
            $this->flagManager->saveFlag(self::FLAG_CODE_VALUE, $pageIdentifier);
            $this->reinitableConfig->reinit();
        }

        return $this;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function revert()
    {
        /** @var Page $page */
        $page = $this->pageFactory->create();
        $page->load($this->flagManager->getFlagData(self::FLAG_CODE_VALUE), PageInterface::IDENTIFIER);
        if ($page->getId()) {
            $this->pageResource->delete($page);
            $this->flagManager->deleteFlag(self::FLAG_CODE_VALUE);
        }
    }

    private function isCanApply(): bool
    {
        /** @var Page $page */
        $page = $this->pageFactory->create();
        $page->load($this->flagManager->getFlagData(self::FLAG_CODE_VALUE), PageInterface::IDENTIFIER);

        return !$page->getId();
    }

    private function getPageIdentifier(): string
    {
        $suffix = 1;
        $urlRewrite = self::IDENTIFIER;

        while ($this->isUrlRewriteExists($urlRewrite)) {
            $urlRewrite = sprintf('%s_%d', self::IDENTIFIER, $suffix);
            $suffix++;
        }

        return $urlRewrite;
    }

    private function isUrlRewriteExists(string $urlRewrite): bool
    {
        $connection = $this->moduleDataSetup->getConnection();
        $select = $connection->select()->from(
            $this->moduleDataSetup->getTable(DbStorage::TABLE_NAME)
        )->where(
            $connection->prepareSqlCondition(UrlRewrite::REQUEST_PATH, ['eq' => $urlRewrite])
        );

        return (bool) $connection->fetchRow($select);
    }
}
