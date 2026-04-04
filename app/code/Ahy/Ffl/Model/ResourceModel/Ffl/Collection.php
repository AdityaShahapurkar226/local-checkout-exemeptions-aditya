<?php
/**
 * Copyright © Ahy Consulting  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Ffl\Model\ResourceModel\Ffl;

class Collection extends \Magento\Eav\Model\Entity\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Ahy\Ffl\Model\Ffl::class,
            \Ahy\Ffl\Model\ResourceModel\Ffl::class
        );
    }
}

