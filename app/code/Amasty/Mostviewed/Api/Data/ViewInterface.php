<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Api\Data;

interface ViewInterface
{
    public const MAIN_TABLE = 'mostviewed_view_temp';
    /**#@+
     * Constants defined for keys of data array
     */
    public const ID = 'id';
    public const VISITOR_ID = 'visitor_id';
    public const BLOCK_ID = 'block_id';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\Mostviewed\Api\Data\ViewInterface
     */
    public function setId($id);

    /**
     * @return string
     */
    public function getVisitorId();

    /**
     * @param string $visitorId
     *
     * @return \Amasty\Mostviewed\Api\Data\ViewInterface
     */
    public function setVisitorId($visitorId);

    /**
     * @return int
     */
    public function getBlockId();

    /**
     * @param int $blockId
     *
     * @return \Amasty\Mostviewed\Api\Data\ViewInterface
     */
    public function setBlockId($blockId);
}
