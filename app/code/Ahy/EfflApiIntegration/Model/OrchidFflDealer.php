<?php
namespace Ahy\EfflApiIntegration\Model;

use Magento\Framework\Model\AbstractModel;

class OrchidFflDealer extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(\Ahy\EfflApiIntegration\Model\ResourceModel\OrchidFflDealer::class);
    }
}
