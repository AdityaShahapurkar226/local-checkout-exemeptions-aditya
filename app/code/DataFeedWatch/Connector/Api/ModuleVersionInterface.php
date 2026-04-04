<?php
/**
 * Created by Q-Solutions Studio
 * Date: 09.12.2019
 *
 * @category    DataFeedWatch
 * @package     DataFeedWatch_Connector
 * @author      Wojciech M. Wnuk <wojtek@qsolutionsstudio.com>
 */

namespace DataFeedWatch\Connector\Api;

/**
 * @api
 */
interface ModuleVersionInterface
{
    /**
     * @method getVersionData
     * @return mixed[]
     */
    public function getVersionData(): array;
}
