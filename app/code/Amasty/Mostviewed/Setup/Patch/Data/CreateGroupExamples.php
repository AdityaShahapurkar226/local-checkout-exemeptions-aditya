<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Setup\Patch\Data;

use Amasty\Mostviewed\Model\GroupFactory;
use Amasty\Mostviewed\Model\ResourceModel\Group\Collection;
use Amasty\Mostviewed\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\App\Area as Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Filesystem\Glob;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use SimpleXMLElement;

class CreateGroupExamples implements DataPatchInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var CollectionFactory
     */
    private $groupCollectionFactory;

    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var Glob
     */
    private $glob;

    /**
     * @var State
     */
    private $appState;

    public function __construct(
        ResourceConnection $resourceConnection,
        CollectionFactory $groupCollectionFactory,
        GroupFactory $groupFactory,
        Glob $glob,
        State $appState
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->groupFactory = $groupFactory;
        $this->glob = $glob;
        $this->appState = $appState;
    }

    /**
     * @return string[]
     */
    public static function getDependencies()
    {
        return [
            ConvertOldSettings::class
        ];
    }

    /**
     * @return string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return CreateGroupExamples
     */
    public function apply()
    {
        return $this->appState->emulateAreaCode(Area::AREA_ADMINHTML, [$this, 'applyCallback'], []);
    }

    /**
     * @return CreateGroupExamples
     */
    public function applyCallback()
    {
        if ($this->isGroupsNotExist()) {
            $paths = $this->getXmlTemplatesPaths();
            foreach ($paths as $path) {
                $xmlDoc = simplexml_load_file($path);
                $templateData = $this->parseNode($xmlDoc);

                $this->createGroup($templateData);
            }
        }

        return $this;
    }

    private function isGroupsNotExist(): bool
    {
        /** @var Collection $groupCollection */
        $groupCollection = $this->groupCollectionFactory->create();
        return !$groupCollection->getSize();
    }

    private function getXmlTemplatesPaths(): array
    {
        $p = strrpos(__DIR__, DIRECTORY_SEPARATOR);
        $directoryPath = $p ? substr(__DIR__, 0, $p) : __DIR__;
        $directoryPath .= '/../../etc/adminhtml/examples/';

        return $this->glob->glob($directoryPath . '*.xml');
    }

    /**
     * @param SimpleXMLElement $node
     * @param $parentKeyNode
     * @return array|string
     */
    private function parseNode(SimpleXMLElement $node, string $parentKeyNode = '')
    {
        $data = [];
        foreach ($node as $keyNode => $childNode) {
            if (is_object($childNode)) {
                $data[$keyNode] = $this->parseNode($childNode, $keyNode);
            }
        }

        if (count($node) == 0) {
            $data = (string)$node;
            if ($data == 'true') {
                $data = true;
            }
        }

        return $data;
    }

    private function createGroup(array $data): void
    {
        $group = $this->groupFactory->create();
        $group->addData($data);
        $group->save();
    }
}
