<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Authorizenet\Api\Data;

interface AhyAuthorizeNetInterface
{

    const ENCRYPTIONKEY = 'EncryptionKey';
    const AHYAUTHORIZENET_ID = 'ahyauthorizenet_id';

    /**
     * Get ahyauthorizenet_id
     * @return string|null
     */
    public function getAhyauthorizenetId();

    /**
     * Set ahyauthorizenet_id
     * @param string $ahyauthorizenetId
     * @return \Ahy\Authorizenet\AhyAuthorizeNet\Api\Data\AhyAuthorizeNetInterface
     */
    public function setAhyauthorizenetId($ahyauthorizenetId);

    /**
     * Get EncryptionKey
     * @return string|null
     */
    public function getEncryptionKey();

    /**
     * Set EncryptionKey
     * @param string $encryptionKey
     * @return \Ahy\Authorizenet\AhyAuthorizeNet\Api\Data\AhyAuthorizeNetInterface
     */
    public function setEncryptionKey($encryptionKey);
}

