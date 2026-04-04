<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Marketplace\Model;

use Magento\Framework\Setup\SampleData\Context as SampleDataContext;

/**
 * Webkul Marketplace Block Model
 */
class CmsBlock
{
    /**
     * @var \Magento\Framework\Setup\SampleData\Context
     */
    private $sampleDataContext;

    /**
     * @var \Magento\Cms\Model\BlockFactory
     */
    private $blockFactory;

    /**
     * @var Block\Converter
     */
    private $converter;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $file;

    /**
     * Constructor
     *
     * @param SampleDataContext $sampleDataContext
     * @param \Magento\Cms\Model\BlockFactory $blockFactory
     * @param Block\Converter $converter
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Filesystem\Driver\File $file
     */
    public function __construct(
        SampleDataContext $sampleDataContext,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        Block\Converter $converter,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Filesystem\Driver\File $file
    ) {
        $this->sampleDataContext = $sampleDataContext;
        $this->blockFactory = $blockFactory;
        $this->converter = $converter;
        $this->categoryRepository = $categoryRepository;
        $this->file = $file;
    }

    /**
     * Function install
     *
     * @param array $fixtures
     * @return void
     */
    public function install(array $fixtures)
    {
        $fixtureManager = $this->sampleDataContext->getFixtureManager();
        $csvReader      = $this->sampleDataContext->getCsvReader();
        foreach ($fixtures as $fileName) {
            $fileName = $fixtureManager->getFixture($fileName);
            if (!$this->file->isExists($fileName)) {
                continue;
            }

            $rows = $csvReader->getData($fileName);
            $headerArray = array_shift($rows);

            foreach ($rows as $row) {
                $data = [];
                foreach ($row as $key => $value) {
                    $data[$headerArray[$key]] = $value;
                }
                $row = $data;
                $data = $this->converter->convertRow($row);
                $cmsBlock = $this->saveCmsBlock($data['block']);
                $cmsBlock->unsetData();
            }
        }
    }

    /**
     * Function saveCmsBlock
     *
     * @param array $data
     * @return \Magento\Cms\Model\Block
     */
    protected function saveCmsBlock($data)
    {
        $cmsBlock = $this->blockFactory->create()->load($data['identifier']);
        if (!$cmsBlock->getData()) {
            $cmsBlock->setData($data);
        } else {
            $cmsBlock->addData($data);
        }
        $defaultStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        $cmsBlock->setStores([$defaultStoreId]);
        $cmsBlock->setIsActive(1);
        $cmsBlock->save();
        return $cmsBlock;
    }

    /**
     * Function setCategoryLandingPage
     *
     * @param string $blockId
     * @param string $categoryId
     * @return void
     */
    protected function setCategoryLandingPage($blockId, $categoryId)
    {
        $categoryCmsData = [
            'landing_page' => $blockId,
            'display_mode' => 'PRODUCTS_AND_PAGE',
        ];
        if (!empty($categoryId)) {
            $category = $this->categoryRepository->get($categoryId);
            $category->setData($categoryCmsData);
            $this->categoryRepository->save($categoryId);
        }
    }
}
