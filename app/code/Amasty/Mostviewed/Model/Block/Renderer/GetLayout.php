<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Automatic Related Products for Magento 2
 */

namespace Amasty\Mostviewed\Model\Block\Renderer;

use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\BuilderFactory as LayoutBuilderFactory;
use Magento\Framework\View\Layout\GeneratorPool;
use Magento\Framework\View\Layout\ReaderPool;

class GetLayout
{
    /**
     * @var Layout[]
     */
    private $layoutPool;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var ReaderPool
     */
    private $layoutReaderPool;

    /**
     * @var GeneratorPool
     */
    private $layoutGeneratorPool;

    /**
     * @var LayoutBuilderFactory
     */
    private $layoutBuilderFactory;

    public function __construct(
        LayoutFactory $layoutFactory,
        ReaderPool $layoutReaderPool,
        GeneratorPool $layoutGeneratorPool,
        LayoutBuilderFactory $layoutBuilderFactory
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->layoutReaderPool = $layoutReaderPool;
        $this->layoutGeneratorPool = $layoutGeneratorPool;
        $this->layoutBuilderFactory = $layoutBuilderFactory;
    }

    /**
     * Create layout with specified handles for each product type.
     * Use custom layout \Amasty\Mostviewed\Model\Block\Renderer\Layout for avoid cache,
     * because one layout used for render options for many products.
     */
    public function execute(string $typeId): Layout
    {
        if (!isset($this->layoutPool[$typeId])) {
            $layout = $this->layoutFactory->create([
                'reader' => $this->layoutReaderPool,
                'generatorPool' => $this->layoutGeneratorPool
            ]);

            $this->layoutBuilderFactory->create(LayoutBuilderFactory::TYPE_PAGE, ['layout' => $layout]);

            $layout->getUpdate()
                ->addHandle('default')
                ->addHandle('catalog_product_view')
                ->addHandle('catalog_product_view_type_' . $typeId);

            $this->layoutPool[$typeId] = $layout;
        }

        return $this->layoutPool[$typeId];
    }
}
