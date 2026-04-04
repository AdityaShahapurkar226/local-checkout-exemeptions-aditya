<?php
namespace Ahy\EfflApiIntegration\Api;

interface FflDealerInterface
{
    /**
     * Get FFL Dealer details with Geo coordinates
     *
     * @return array
     */
    public function getDealerWithGeo();
}
