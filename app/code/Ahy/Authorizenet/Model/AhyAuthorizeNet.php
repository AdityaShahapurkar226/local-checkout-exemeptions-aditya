<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Authorizenet\Model;

use Ahy\Authorizenet\Api\Data\AhyAuthorizeNetInterface;
use Magento\Framework\Model\AbstractModel;

class AhyAuthorizeNet extends AbstractModel implements AhyAuthorizeNetInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Ahy\Authorizenet\Model\ResourceModel\AhyAuthorizeNet::class);
    }

    /**
     * @inheritDoc
     */
    public function getAhyauthorizenetId()
    {
        return $this->getData(self::AHYAUTHORIZENET_ID);
    }

    /**
     * @inheritDoc
     */
    public function setAhyauthorizenetId($ahyauthorizenetId)
    {
        return $this->setData(self::AHYAUTHORIZENET_ID, $ahyauthorizenetId);
    }

    /**
     * @inheritDoc
     */
    public function getEncryptionKey()
    {
        return $this->getData(self::ENCRYPTIONKEY);
    }

    /**
     * @inheritDoc
     */
    public function setEncryptionKey($encryptionKey)
    {
        return $this->setData(self::ENCRYPTIONKEY, $encryptionKey);
    }
}

