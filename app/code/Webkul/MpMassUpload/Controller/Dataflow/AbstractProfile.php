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
namespace Webkul\MpMassUpload\Controller\Dataflow;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\RequestInterface;
use Webkul\MpMassUpload\Model\AttributeProfile;
use Webkul\MpMassUpload\Api\AttributeProfileRepositoryInterface;
use Webkul\MpMassUpload\Model\AttributeMappingFactory;
use Webkul\MpMassUpload\Api\AttributeMappingRepositoryInterface;

/**
 * Webkul MpMassUpload AbstractProfile Controller
 */
abstract class AbstractProfile extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var AttributeProfile
     */
    protected $_attributeProfile;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var AttributeProfileRepositoryInterface
     */
    protected $_attributeProfileRepository;

    /**
     * @var AttributeMapping
     */
    protected $_attributeMapping;

    /**
     * @var AttributeMappingRepositoryInterface
     */
    protected $_attributeMappingRepository;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @param Context                                    $context
     * @param PageFactory                                $resultPageFactory
     * @param \Magento\Customer\Model\Url                $url
     * @param \Magento\Customer\Model\Session            $customerSession
     * @param FormKeyValidator                           $formKeyValidator
     * @param AttributeProfile                           $attributeProfile
     * @param Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param AttributeProfileRepositoryInterface        $attributeProfileRepository
     * @param AttributeMapping                           $attributeMapping
     * @param AttributeMappingRepositoryInterface        $attributeMappingRepository
     * @param \Webkul\Marketplace\Helper\Data            $marketplaceHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $customerSession,
        FormKeyValidator $formKeyValidator,
        AttributeProfile $attributeProfile,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        AttributeProfileRepositoryInterface $attributeProfileRepository,
        AttributeMappingFactory $attributeMapping,
        AttributeMappingRepositoryInterface $attributeMappingRepository,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_url = $url;
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_attributeProfile = $attributeProfile;
        $this->_attributeProfileRepository = $attributeProfileRepository;
        $this->_date = $date;
        $this->_attributeMapping = $attributeMapping;
        $this->_attributeMappingRepository = $attributeMappingRepository;
        $this->marketplaceHelper = $marketplaceHelper;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }
}
