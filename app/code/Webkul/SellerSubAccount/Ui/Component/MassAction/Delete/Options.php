<?php
/**
 * Webkul Software.
 *
 * @category Marketplace
 * @package  Webkul_SellerSubAccount
 * @author   Webkul
 * @license  https://store.webkul.com/license.html
 */

namespace Webkul\SellerSubAccount\Ui\Component\MassAction\Delete;

use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;
use Magento\Framework\App\RequestInterface;

/**
 * Class Options massaction delete option
 */
class Options implements JsonSerializable
{
    /**
     * @var array
     */
    public $_options;

    /**
     * @var RequestInterface
     */
    public $_request;

    /**
     * Additional options params
     *
     * @var array
     */
    public $_data;

    /**
     * @var UrlInterface
     */
    public $_urlBuilder;

    /**
     * Base URL for subactions
     *
     * @var string
     */
    public $_urlPath;

    /**
     * Param name for subactions
     *
     * @var string
     */
    public $_paramName;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param UrlInterface     $urlBuilder
     * @param array            $data
     */
    public function __construct(
        RequestInterface $request,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->_request = $request;
        $this->_data = $data;
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * @inheritdoc
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        if ($this->_options === null) {
            $this->prepareData();
            $sellerId = $this->_request->getParam('seller_id');
            $this->_options[$sellerId] = [
                'type' => 'seller_account_' . $sellerId,
                'label' => __('Delete'),
            ];
            if ($this->_urlPath && $this->_paramName) {
                $this->_options[$sellerId]['url'] = $this->_urlBuilder->getUrl(
                    $this->_urlPath,
                    [$this->_paramName => $sellerId]
                );
            }
            $this->_options = array_values($this->_options);
        }
        return $this->_options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    public function prepareData()
    {
        foreach ($this->_data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->_urlPath = $value;
                    break;
                case 'paramName':
                    $this->_paramName = $value;
                    break;
            }
        }
    }
}
