<?php
/**
 * Copyright © Ahy Consulting  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ahy\Ffl\Api\Data;

interface FflInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const TITLE = 'title';
    const ENTITY_ID = 'entity_id';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Ahy\Ffl\Api\Data\FflInterface
     */
    public function setEntityId($entityId);

    /**
     * Get title
     * @return string|null
     */
    public function getTitle();

    /**
     * Set title
     * @param string $title
     * @return \Ahy\Ffl\Api\Data\FflInterface
     */
    public function setTitle($title);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Ahy\Ffl\Api\Data\FflExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Ahy\Ffl\Api\Data\FflExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Ahy\Ffl\Api\Data\FflExtensionInterface $extensionAttributes
    );
}

