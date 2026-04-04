<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAssignProduct
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAssignProduct\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;

class AssignProductSection implements SectionSourceInterface
{
    /**
     * Get Section Data
     *
     * @return void
     */
    public function getSectionData()
    {
        return [
            'msg' =>'Data from section',
        ];
    }
}
