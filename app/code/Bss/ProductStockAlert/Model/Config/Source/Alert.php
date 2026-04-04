<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlert
 * @author     Extension Team
 * @copyright  Copyright (c) 2015-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductStockAlert\Model\Config\Source;

class Alert extends \Bss\ProductStockAlert\Model\Config\Source\FieldConfig
{
    /**
     * Cron string path
     */
    const CRON_STRING_PATH_PRODUCT_STOCK_ALERT = 'crontab/default/jobs/bss_product_stock_alert/schedule/cron_expr';

    /**
     * Cron model path
     */
    const CRON_MODEL_PATH_PRODUCT_STOCK_ALERT = 'crontab/default/jobs/bss_product_stock_alert/run/model';

    /**
     * {@inheritdoc}
     *
     * @return $this
     * @throws \Exception
     */
    public function afterSave()
    {
        $cronSet = $this->getData('groups/productstockalert_cron/fields/cron_period/value');

        try {
            $this->_configValueFactory->create()->load(
                self::CRON_STRING_PATH_PRODUCT_STOCK_ALERT,
                'path'
            )->setValue(
                $cronSet
            )->setPath(
                self::CRON_STRING_PATH_PRODUCT_STOCK_ALERT
            )->save();
            $this->_configValueFactory->create()->load(
                self::CRON_MODEL_PATH_PRODUCT_STOCK_ALERT,
                'path'
            )->setValue(
                $this->_runModelPath
            )->setPath(
                self::CRON_MODEL_PATH_PRODUCT_STOCK_ALERT
            )->save();
        } catch (\Exception $e) {
            throw new \NoSuchElementException(__('We can\'t save the cron expression.'));
        }

        return parent::afterSave();
    }
}
