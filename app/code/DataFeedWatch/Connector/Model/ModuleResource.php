<?php
/**
 * Created by Q-Solutions Studio
 *
 * @category    DataFeedWatch
 * @package     DataFeedWatch_Connector
 * @author      Wojciech M. Wnuk <wojtek@qsolutionsstudio.com>
 */

namespace DataFeedWatch\Connector\Model;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class ModuleResource
 * @package DataFeedWatch\Connector\Model
 */
class ModuleResource extends \Magento\Framework\Module\ModuleResource
{
    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;
    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * ModuleResource constructor.
     * @param Context $context
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param ReadFactory $readFactory
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        ComponentRegistrarInterface $componentRegistrar,
        ReadFactory $readFactory,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
    }

    /**
     * @return false|mixed|string
     */
    public function getComposerVersion()
    {
        $path = $this->componentRegistrar->getPath(
            ComponentRegistrar::MODULE,
            ModuleVersion::MODULE
        );
        $directoryRead = $this->readFactory->create($path);
        try {
            $composerJsonData = $directoryRead->readFile('composer.json');
            $data = json_decode($composerJsonData);
        } catch (\Exception $e) {
            return $this->getDbVersion(ModuleVersion::MODULE);
        }

        return !empty($data->version) ? $data->version : $this->getDbVersion(ModuleVersion::MODULE);
    }
}