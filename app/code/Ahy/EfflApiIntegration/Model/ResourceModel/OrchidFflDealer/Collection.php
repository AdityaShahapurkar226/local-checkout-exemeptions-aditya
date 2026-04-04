<?php
namespace Ahy\EfflApiIntegration\Model\ResourceModel\OrchidFflDealer;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Ahy\EfflApiIntegration\Model\OrchidFflDealer;
use Ahy\EfflApiIntegration\Model\ResourceModel\OrchidFflDealer as OrchidFflDealerResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';

    protected function _construct()
    {
        $this->_init(OrchidFflDealer::class, OrchidFflDealerResource::class);
    }

}
