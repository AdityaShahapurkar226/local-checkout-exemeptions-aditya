<?php

namespace Ahy\ProductExport\Console\Command;

use Magento\Framework\App\State;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Serialize\Serializer\JsonFactory;
use Psr\Log\LoggerInterface;

class ExportProductsOptimized extends Command
{
    private State $state;
    private CollectionFactory $productCollectionFactory;
    private JsonFactory $jsonSerializerFactory;
    private LoggerInterface $logger;

    public function __construct(
        State $state,
        CollectionFactory $productCollectionFactory,
        JsonFactory $jsonSerializerFactory,
        LoggerInterface $logger
    ) {
        $this->state = $state;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->jsonSerializerFactory = $jsonSerializerFactory;
        $this->logger = $logger;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('catalog:export:products')
            ->setDescription('Export products to ONE streaming JSON file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode('frontend');
        } catch (\Exception $e) {
            // already set
        }

        $pageSize = 500;
        $totalExported = 0;

        $filePath = BP . '/var/export/product_catalog_optimized.json';

        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0775, true);
        }

        $fp = fopen($filePath, 'w');
        if (!$fp) {
            $output->writeln('<error>Cannot open export file</error>');
            return Command::FAILURE;
        }

        fwrite($fp, "[\n");
        $isFirst = true;

        $output->writeln('<info>Starting optimized product export…</info>');

        $jsonSerializer = $this->jsonSerializerFactory->create();

        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect([
            'name',
            'price',
            'status',
            'visibility',
            'weight',
            'description',
            'sku',
            'attribute_set_id',
            'type_id'
        ]);
        $collection->setPageSize($pageSize);

        // ✅ Stock join is valid
        $collection->joinTable(
            'cataloginventory_stock_item',
            'product_id=entity_id',
            [
                'qty' => 'qty',
                'is_in_stock' => 'is_in_stock'
            ],
            null,
            'left'
        );

        $lastPage   = $collection->getLastPageNumber();
        $totalCount = $collection->getSize();

        for ($currentPage = 1; $currentPage <= $lastPage; $currentPage++) {
            $collection->setCurPage($currentPage);
            $collection->load();

            if (!$collection->count()) {
                break;
            }

            foreach ($collection as $product) {
                try {
                    /** --------------------
                     * Media Gallery (SAFE)
                     * ------------------- */
                    $media = [];
                    $galleryEntries = $product->getMediaGalleryEntries();
                    if ($galleryEntries) {
                        foreach ($galleryEntries as $entry) {
                            $media[] = [
                                'file'       => $entry->getFile(),
                                'media_type' => $entry->getMediaType(),
                                'label'      => $entry->getLabel(),
                                'position'   => $entry->getPosition(),
                                'disabled'   => $entry->getDisabled(),
                            ];
                        }
                    }

                    /** --------------------
                     * JSON STRUCTURE (UNCHANGED)
                     * ------------------- */
                    $data = [
                        'id'               => $product->getId(),
                        'sku'              => $product->getSku(),
                        'type'             => $product->getTypeId(),
                        'attribute_set_id' => $product->getAttributeSetId(),
                        'name'             => $product->getName(),
                        'price'            => (float) $product->getPrice(),
                        'status'           => $product->getStatus(),
                        'visibility'       => $product->getVisibility(),
                        'weight'           => $product->getWeight(),
                        'description'      => $product->getDescription(),
                        'qty'              => (float) $product->getQty(),
                        'is_in_stock'      => (bool) $product->getIsInStock(),
                        'media_gallery_entries' => $media,
                    ];

                    if (!$isFirst) {
                        fwrite($fp, ",\n");
                    }

                    fwrite($fp, $jsonSerializer->serialize($data));
                    $isFirst = false;

                    $totalExported++;

                } catch (\Throwable $e) {
                    $this->logger->error(
                        "Skipped product ID {$product->getId()}: " . $e->getMessage()
                    );
                }
            }

            $output->writeln(
                "Page $currentPage processed ($totalExported / $totalCount)"
            );

            $collection->clear();
        }

        fwrite($fp, "\n]");
        fclose($fp);

        $output->writeln("<info>✔ Optimized Export completed</info>");
        $output->writeln("<info>Total products: $totalExported</info>");
        $output->writeln("<info>File: $filePath</info>");

        return 0;
    }
}
