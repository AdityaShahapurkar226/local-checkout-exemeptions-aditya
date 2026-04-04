<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_AdminLoginAsVendor
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\AdminLoginAsVendor\Plugin;

use Magento\Framework\App\ResourceConnection;
use Magento\LoginAsCustomerAdminUi\Ui\Customer\Component\Button\DataProvider;

class LoginAsCustomer
{
    /**
     * Construct function
     *
     * @param ResourceConnection $isAssistanceEnabled
     */
    public function __construct(
        ResourceConnection $isAssistanceEnabled
    ) {
        $this->isAssistanceEnabled = $isAssistanceEnabled;
    }
    /**
     * AfterGetData function
     *
     * @param DataProvider $subject
     * @param array $result
     * @param integer $customerId
     */
    public function afterGetData(DataProvider $subject, array $result, int $customerId)
    {
        $connection = $this->isAssistanceEnabled->getConnection();
        $tableName = $this->isAssistanceEnabled->getTableName('login_as_customer_assistance_allowed');
        $connection->insertOnDuplicate(
            $tableName,
            [
                'customer_id' => $customerId
            ]
        );

        return $result;
    }
}
