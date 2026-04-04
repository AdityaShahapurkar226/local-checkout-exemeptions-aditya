<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Plugin;

class ElasticsearchFilterPlugin
{

    /**
     * Initialization
     *
     * @param \Webkul\Marketplace\Model\SellerIdDataMapper $sellerIdDataMapper
     */
    public function __construct(
        \Webkul\Marketplace\Model\SellerIdDataMapper $sellerIdDataMapper
    ) {
        $this->sellerIdDataMapper = $sellerIdDataMapper;
    }

    /**
     * Add seller id mapper
     *
     * @param mixed $subject
     * @param array $documents
     * @param int   $storeId
     * @param int   $mappedIndexerId
     *
     * @return array
     */
    public function beforeAddDocs($subject, array $documents, $storeId, $mappedIndexerId)
    {
        $documents = $this->sellerIdDataMapper->map($documents, $storeId, $mappedIndexerId);

        return [$documents, $storeId, $mappedIndexerId];
    }
}
