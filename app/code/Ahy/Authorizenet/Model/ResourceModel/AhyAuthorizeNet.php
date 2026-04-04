<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Authorizenet\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class AhyAuthorizeNet extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('ahy_authorizenet_ahyauthorizenet', 'ahyauthorizenet_id');
    }
}

