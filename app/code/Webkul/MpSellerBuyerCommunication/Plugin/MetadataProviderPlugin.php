<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerBuyerCommunication
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpSellerBuyerCommunication\Plugin;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Component\Filters;
use Magento\Ui\Component\Filters\Type\Select;
use Magento\Ui\Component\Listing\Columns;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class MetadataProviderPlugin extends \Magento\Ui\Model\Export\MetadataProvider
{
    
    /**
     * Constructor
     *
     * @param Filter $filter
     * @param TimezoneInterface $localeDate
     * @param ResolverInterface $localeResolver
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @param string $dateFormat = 'M j, Y h:i:s A'
     * @param array $data
     */
    public function __construct(
        Filter $filter,
        TimezoneInterface $localeDate,
        ResolverInterface $localeResolver,
        \Magento\Catalog\Model\ProductFactory $product,
        $dateFormat = 'M j, Y h:i:s A',
        array $data = []
    ) {
        parent::__construct($filter, $localeDate, $localeResolver, $dateFormat, $data);
        $this->product = $product;
    }
    
    /**
     * Get Row Data
     *
     * @param DocumentInterface $document
     * @param array $fields
     * @param array $options
     * @return array
     */
    public function getRowData(DocumentInterface $document, $fields, $options): array
    {
        $row = [];
        $key = array_search('product_id', $fields);
        foreach ($fields as $column) {
            if (isset($options[$column])) {
               
                $key = $document->getCustomAttribute($column)->getValue();
                if (isset($options[$column][$key])) {
                    $row[] = $options[$column][$key];
                } else {
                    $row[] = '';
                }
            } else {
                $row[] = $document->getCustomAttribute($column)->getValue();
                if ($column == 'product_id') {
               
                    if (isset($row[$key]) && $row[$key]!=0) {
                        $product = $this->loadProduct($row[$key]);
                        if ($product->getEntityId()) {
                            $row[$key] = $product->getName();
                        }
                    } else {
                        $row[$key] = __('None');
                    }
                }
            }
        }

        return $row;
    }
   /**
    * Load Product
    *
    * @param object $item
    * @return void
    */
    public function loadProduct($item)
    {
        return $this->product->create()->load($item);
    }
}
