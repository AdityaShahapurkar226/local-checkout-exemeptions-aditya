<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\ModuleListInterface;

class Information extends Fieldset
{
    public const MESSAGE_CHECKER_PATH = 'ammostviewed/general/first_massage_view';

    public const IF_MODULE_WAS_INSTALLED = 'ammostviewed/related_products/enabled';

    /**
     * @var string
     */
    private $userGuide = 'https://amasty.com/docs/doku.php?id=magento_2:automatic_related_products';

    /**
     * @var array
     */
    private $enemyExtensions = [];

    /**
     * @var string
     */
    private $content;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        ModuleListInterface $moduleList,
        \Magento\Framework\App\Config\ReinitableConfigInterface $reinitableConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->moduleList = $moduleList;
        $this->reinitableConfig = $reinitableConfig;
        $this->configWriter = $configWriter;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Render fieldset html
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

        $this->setContent(__('Please update Amasty Base module. Re-upload it and replace all the files.'));

        $this->_eventManager->dispatch(
            'amasty_base_add_information_content',
            ['block' => $this]
        );

        $html .= $this->getContent();
        $html .= $this->_getFooterHtml($element);

        $html = str_replace(
            'amasty_information]" type="hidden" value="0"',
            'amasty_information]" type="hidden" value="1"',
            $html
        );
        $html = preg_replace('(onclick=\"Fieldset.toggleCollapse.*?\")', '', $html);

        return $html;
    }

    /**
     * @return array|string
     */
    public function getAdditionalModuleContent()
    {
        $result = [];
        if (!$this->_scopeConfig->getValue(self::MESSAGE_CHECKER_PATH)
            && $this->_scopeConfig->getValue(self::IF_MODULE_WAS_INSTALLED)
        ) {
            $this->configWriter->save(self::MESSAGE_CHECKER_PATH, true);
            $this->reinitableConfig->reinit();

            $message = [
                'type' => 'message-error',
                'text' => __(
                    'The extension code has been completely renovated in v2.0.0. '
                    . 'After update all the rules created previously become disabled. '
                    . 'Please recheck the rules configuration before enabling them.'
                )
            ];

            if ($this->getBaseVersion() < '1.3.4') {
                return $message['text'];
            }

            $result[] = $message;
        }

        if ($this->moduleManager->isEnabled('Magento_GraphQl')
            && !$this->moduleManager->isEnabled('Amasty_MostviewedGraphQl')
        ) {
            $message = [
                'type' => 'message-notice',
                'text' => __('Enable customers-also-viewed-graphql module to '
                    . 'activate GraphQl and Automatic Related Products integration. '
                    . 'Please, run the following command in the SSH: '
                    . 'composer require amasty/customers-also-viewed-graphql')
            ];

            $result[] = $message;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getUserGuide()
    {
        return $this->userGuide;
    }

    /**
     * @param string $userGuide
     */
    public function setUserGuide($userGuide)
    {
        $this->userGuide = $userGuide;
    }

    /**
     * @return array
     */
    public function getEnemyExtensions()
    {
        return $this->enemyExtensions;
    }

    /**
     * @param array $enemyExtensions
     */
    public function setEnemyExtensions($enemyExtensions)
    {
        $this->enemyExtensions = $enemyExtensions;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    private function getBaseVersion()
    {
        $version = '';
        if (isset($this->moduleList->getOne('Amasty_Base')['setup_version'])) {
            $version = $this->moduleList->getOne('Amasty_Base')['setup_version'];
        }

        return $version;
    }
}
