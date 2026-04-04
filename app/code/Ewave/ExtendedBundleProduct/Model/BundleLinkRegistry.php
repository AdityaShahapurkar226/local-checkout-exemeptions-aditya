<?php

namespace Ewave\ExtendedBundleProduct\Model;

use Magento\Bundle\Api\Data\LinkInterface;

class BundleLinkRegistry
{
    /**
     * @var LinkInterface
     */
    private $link;

    /**
     * @param LinkInterface $link
     * @return void
     */
    public function setLink(LinkInterface $link)
    {
        $this->link = $link;
    }

    /**
     * @return LinkInterface|null
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return void
     */
    public function clearLink()
    {
        $this->link = null;
    }
}
