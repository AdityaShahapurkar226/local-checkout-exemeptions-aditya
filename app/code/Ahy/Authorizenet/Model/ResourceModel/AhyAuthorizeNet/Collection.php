<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Authorizenet\Model\ResourceModel\AhyAuthorizeNet;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'ahyauthorizenet_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Ahy\Authorizenet\Model\AhyAuthorizeNet::class,
            \Ahy\Authorizenet\Model\ResourceModel\AhyAuthorizeNet::class
        );
    }
}

