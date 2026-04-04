<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpMassUpload\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class MpMassUpload Customerlink.
 */
class Customerlink extends Column
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @param ContextInterface            $context
     * @param UiComponentFactory          $uiComponentFactory
     * @param UrlInterface                $urlBuilder
     * @param CustomerRepositoryInterface $customerRepository
     * @param array                       $components
     * @param array                       $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        CustomerRepositoryInterface $customerRepository,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->_customerRepository = $customerRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as $key => $item) {
                if (isset($item['seller_id']) && $item['seller_id'] != 0) {
                    $seller = $this->_customerRepository->getById(
                        $item['seller_id']
                    );
                    $sellerName = $seller->getFirstname()." ".$seller->getLastname();
                    $dataSource['data']['items'][$key]['seller_id'] = "<a href='".$this->_urlBuilder->getUrl(
                        'customer/index/edit',
                        ['id' => $item['seller_id']]
                    )."' target='blank' title='".__('View Customer')."'>".$sellerName.'</a>';
                } else {
                    $dataSource['data']['items'][$key]['seller_id'] = __('Admin');
                }
            }
        }
        return $dataSource;
    }
}
