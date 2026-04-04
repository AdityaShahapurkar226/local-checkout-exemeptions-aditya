<?php
/**
 * Copyright © Ahy Consulting  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Ffl\Model\ResourceModel;

class Ffl extends \Magento\Eav\Model\Entity\AbstractEntity
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setType('ahy_ffl_entity');
    }
}

