<?php

namespace Xigen\ProductUpload\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Import helper class
 */
class Import extends AbstractHelper
{
    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory
     */
    protected $productInterfaceFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepositoryInterface;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Customer\Model\GroupFactory
     */
    protected $groupFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $product;

    /**
     * @var \Xigen\ProductUpload\Helper\Indexer
     */
    protected $indexer;

    /**
     * @var \Xigen\ProductUpload\Helper\Option
     */
    protected $option;

    /**
     * Import constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Api\Data\ProductInterfaceFactory $productInterfaceFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Model\GroupFactory $groupFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Xigen\ProductUpload\Helper\Indexer $indexer
     * @param \Xigen\ProductUpload\Helper\Option $option
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productInterfaceFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\GroupFactory $groupFactory,
        \Magento\Catalog\Model\ProductFactory $product,
        \Xigen\ProductUpload\Helper\Indexer $indexer,
        \Xigen\ProductUpload\Helper\Option $option
    ) {
        $this->productInterfaceFactory = $productInterfaceFactory;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->logger = $logger;
        $this->groupFactory = $groupFactory;
        $this->product = $product;
        $this->indexer = $indexer;
        $this->option = $option;
        parent::__construct($context);
    }

    /**
     * Get product by SKU
     * @return \Magento\Catalog\Model\Data\Product
     */
    public function get($sku, $editMode = false, $storeId = null, $forceReload = false)
    {
        try {
            return $this->productRepositoryInterface->get($sku, $editMode, $storeId, $forceReload);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
        }
    }

    /**
     * Get product by SKU
     * @param string $sku
     * @param int $storeId
     * @return \Magento\Catalog\Model\Product
     */
    public function getBySku($sku, $storeId = null)
    {
        try {
            $product = $this->productRepositoryInterface->get($sku, false, $storeId, false);
            $loaded = $this->product->create()->load($product->getId());
            $loaded->setStoreId($storeId);
            return $loaded;
        } catch (\Exception $e) {
            // $this->logger->critical($e);
            return false;
        }
    }

    /**
     * Update attribute data
     * @param array $csvData
     * @return void
     */
    public function updateAttributeData($csvData = []) // phpcs:ignore
    {
        $productIds = [];
        foreach ($csvData as $csvRow) {
            $product = $this->getBySku($csvRow['sku'], $csv['store_id'] ?? 0);
            if ($product) {
                // indexing
                $productIds[$product->getId()] = $product->getId();
                // dont set these
                unset($csvRow['sku']);
                unset($csvRow['store_id']);
                foreach ($csvRow as $attributeCode => $value) {
                    // selects and multiselects
                    $attribute = $product->getResource()->getAttribute($attributeCode);
                    if ($attribute &&
                        $attributeCode != 'visibility' &&
                        $attributeCode != 'status' &&
                        $attributeCode != 'country_of_manufacture' &&
                        ($attribute->getFrontendInput() == 'multiselect' ||
                         $attribute->getFrontendInput() == 'select')) {
                        $optionIds = [];

                        $values = explode(",", $value);

                        foreach ($values as $val) {
                            $optionId = $this->option->createOrGetId($attributeCode, trim((string) $val));
                            if ($optionId) {
                                $optionIds[] = $optionId;
                            }
                        }

                        $value = implode(',', $optionIds);
                    }

                    // skip if no change
                    if ($value === $product->getData($attributeCode)) {
                        continue;
                    }

                    $product->setData($attributeCode, $value);
                }

                try {
                    $product->save();
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            }
        }

        if (!empty($productIds)) {
            $this->indexer->reindexProductEav($productIds);
            $this->indexer->reindexPrice($productIds);
        }
    }
}
