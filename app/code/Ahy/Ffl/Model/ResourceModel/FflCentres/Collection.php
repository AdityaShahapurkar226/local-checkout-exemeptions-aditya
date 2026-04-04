<?php
/**
 * Copyright © Ahy Consulting  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Ffl\Model\ResourceModel\FflCentres;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'fflcentres_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Ahy\Ffl\Model\FflCentres::class,
            \Ahy\Ffl\Model\ResourceModel\FflCentres::class
        );
    }
}

