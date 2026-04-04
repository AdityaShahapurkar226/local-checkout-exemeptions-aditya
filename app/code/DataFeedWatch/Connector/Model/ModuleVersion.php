<?php
/**
 * Created by Q-Solutions Studio
 * Date: 09.12.2019
 *
 * @category    DataFeedWatch
 * @package     DataFeedWatch_Connector
 * @author      Wojciech M. Wnuk <wojtek@qsolutionsstudio.com>
 */

namespace DataFeedWatch\Connector\Model;

use DataFeedWatch\Connector\Api\ModuleVersionInterface;
use DataFeedWatch\Connector\Model\ModuleResource as ResourceInterface;

class ModuleVersion implements ModuleVersionInterface
{
    const MODULE = 'DataFeedWatch_Connector';

    /**
     * @var ResourceInterface
     */
    protected $moduleResource;

    /**
     * @method __construct
     * @param ResourceInterface $moduleResource
     */
    public function __construct(ResourceInterface $moduleResource)
    {
        $this->moduleResource = $moduleResource;
    }

    /**
     * @method getVersionData
     * @return array
     */
    public function getVersionData() : array
    {
        $versionData = [];
        $versionData['name'] = self::MODULE;
        $versionData['version'] = $this->moduleResource->getDbVersion(self::MODULE);
        $versionData['composer_version'] = $this->moduleResource->getComposerVersion();

        return [$versionData];
    }
}
