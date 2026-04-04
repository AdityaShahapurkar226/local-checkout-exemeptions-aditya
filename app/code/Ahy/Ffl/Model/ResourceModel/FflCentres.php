<?php
/**
 * Copyright © Ahy Consulting  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Ffl\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class FflCentres extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('ahy_ffl_fflcentres', 'fflcentres_id');
    }
}

