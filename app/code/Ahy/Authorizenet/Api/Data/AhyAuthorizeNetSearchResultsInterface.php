<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Authorizenet\Api\Data;

interface AhyAuthorizeNetSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get AhyAuthorizeNet list.
     * @return \Ahy\Authorizenet\Api\Data\AhyAuthorizeNetInterface[]
     */
    public function getItems();

    /**
     * Set EncryptionKey list.
     * @param \Ahy\Authorizenet\Api\Data\AhyAuthorizeNetInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

