<?php

namespace Ahy\EstateApiIntegration\Model;

use Ahy\EstateApiIntegration\Api\Data\RestrictionResponseInterface;
use Magento\Framework\DataObject;

class RestrictionResponse extends DataObject implements RestrictionResponseInterface
{
    public function getRestricted()
    {
        return (bool) $this->getData('restricted');
    }

    public function setRestricted($restricted)
    {
        return $this->setData('restricted', (bool) $restricted);
    }

    public function getReason()
    {
        return $this->getData('reason');
    }

    public function setReason($reason)
    {
        return $this->setData('reason', $reason);
    }

    public function getIsKnife()
    {
        return (bool) $this->getData('is_knife');
    }

    public function setIsKnife($isKnife)
    {
        return $this->setData('is_knife', (bool) $isKnife);
    }
}
