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
namespace Webkul\MpMassUpload\Controller\Adminhtml\Upload;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Webkul\MpMassUpload\Controller\Adminhtml\Upload
{
    /**
     * Controller Execute
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $content = $resultPage->getLayout()->createBlock(\Webkul\MpMassUpload\Block\Adminhtml\Upload\Upload::class);
        $resultPage->addContent($content);
        $resultPage->setActiveMenu('Webkul_MpMassUpload::upload');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Mass Upload'));
        return $resultPage;
    }
}
