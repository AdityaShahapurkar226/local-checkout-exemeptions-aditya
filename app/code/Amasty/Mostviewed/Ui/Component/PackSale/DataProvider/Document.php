<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Ui\Component\PackSale\DataProvider;

use Amasty\Mostviewed\Model\Pack\IsExist as IsPackExist;
use Amasty\Mostviewed\Model\ResourceModel\Pack\Analytic\PackSales\Table;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\AttributeValueFactory;

class Document extends \Magento\Framework\View\Element\UiComponent\DataProvider\Document
{
    /**
     * @var IsPackExist
     */
    private $isPackExist;

    public function __construct(
        IsPackExist $isPackExist,
        AttributeValueFactory $attributeValueFactory
    ) {
        parent::__construct($attributeValueFactory);
        $this->isPackExist = $isPackExist;
    }

    /**
     * @param string $attributeCode
     * @return AttributeInterface
     */
    public function getCustomAttribute($attributeCode)
    {
        switch ($attributeCode) {
            case Table::PACK_NAME_COLUMN:
                if (!$this->isPackExist->execute((int) $this->getData(Table::PACK_ID_COLUMN))) {
                    $this->setCustomAttribute(
                        Table::PACK_NAME_COLUMN,
                        __('%1 (Removed)', $this->getData(Table::PACK_NAME_COLUMN))
                    );
                }
                break;
        }
        return parent::getCustomAttribute($attributeCode);
    }
}
