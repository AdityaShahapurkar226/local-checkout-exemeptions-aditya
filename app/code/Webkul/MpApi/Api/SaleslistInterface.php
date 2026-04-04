<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpApi
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpApi\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface SaleslistInterface
{
  /**
   * Get seller details
   *
   * @api
   *
   * @param int $id
   * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
   * @return \Magento\Framework\Api\SearchResultsInterfaceFactory
   */
    public function getList($id, \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
