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
namespace Webkul\MpMassUpload\Controller\Adminhtml\Dataflow;

use Magento\Backend\App\Action\Context;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;

use Magento\Framework\App\RequestInterface;
use Webkul\MpMassUpload\Model\AttributeProfile;
use Webkul\MpMassUpload\Api\AttributeProfileRepositoryInterface;
use Webkul\MpMassUpload\Model\AttributeMappingFactory;
use Webkul\MpMassUpload\Api\AttributeMappingRepositoryInterface;

/**
 * Webkul MpMassUpload AbstractProfile Controller
 */
abstract class AbstractProfile extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $_resultPage;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

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
     * @param Context                                    $context
     * @param PageFactory                                $resultPageFactory
     * @param ForwardFactory                             $resultForwardFactory
     * @param \Magento\Framework\Registry                $coreRegistry
     * @param AttributeProfile                           $attributeProfile
     * @param Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param AttributeProfileRepositoryInterface        $attributeProfileRepository
     * @param AttributeMapping                           $attributeMapping
     * @param AttributeMappingRepositoryInterface        $attributeMappingRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        \Magento\Framework\Registry $coreRegistry,
        AttributeProfile $attributeProfile,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        AttributeProfileRepositoryInterface $attributeProfileRepository,
        AttributeMappingFactory $attributeMapping,
        AttributeMappingRepositoryInterface $attributeMappingRepository
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_attributeProfile = $attributeProfile;
        $this->_attributeProfileRepository = $attributeProfileRepository;
        $this->_date = $date;
        $this->_attributeMapping = $attributeMapping;
        $this->_attributeMappingRepository = $attributeMappingRepository;
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpMassUpload::dataflow_profile');
    }
}
