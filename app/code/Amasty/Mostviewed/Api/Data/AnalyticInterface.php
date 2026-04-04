<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Api\Data;

interface AnalyticInterface
{
    public const MAIN_TABLE = 'mostviewed_analytics';
    /**#@+
     * Constants defined for keys of data array
     */
    public const ID = 'id';
    public const TYPE = 'type';
    public const COUNTER = 'counter';
    public const BLOCK_ID = 'block_id';
    public const VERSION_ID = 'version_id';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\Mostviewed\Api\Data\AnalyticInterface
     */
    public function setId($id);

    /**
     * @return string|null
     */
    public function getType();

    /**
     * @param string|null $type
     *
     * @return \Amasty\Mostviewed\Api\Data\AnalyticInterface
     */
    public function setType($type);

    /**
     * @return int
     */
    public function getCounter();

    /**
     * @param int $counter
     *
     * @return \Amasty\Mostviewed\Api\Data\AnalyticInterface
     */
    public function setCounter($counter);

    /**
     * @return int
     */
    public function getBlockId();

    /**
     * @param int $blockId
     *
     * @return \Amasty\Mostviewed\Api\Data\AnalyticInterface
     */
    public function setBlockId($blockId);

    /**
     * @return int
     */
    public function getVersionId();

    /**
     * @param int $versionId
     *
     * @return \Amasty\Mostviewed\Api\Data\AnalyticInterface
     */
    public function setVersionId($versionId);
}
