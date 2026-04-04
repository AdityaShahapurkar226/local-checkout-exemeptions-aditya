<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Rule\Condition;

class WhereCombine extends Combine
{
    public function getPrefix(): string
    {
        return 'where_conditions';
    }
}
