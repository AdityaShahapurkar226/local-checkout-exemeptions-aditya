<?php
namespace Ahy\EfflApiIntegration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OrchidFflDealer extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('ahy_orchid_ffl_dealers', 'entity_id');
    }
}
