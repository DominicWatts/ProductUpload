<?php

namespace Xigen\ProductUpload\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Indexer extends AbstractHelper
{
    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Category\Processor
     */
    protected $categoryProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Eav\Processor
     */
    protected $eavProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $priceProcessor;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $stockProcessor;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Catalog\Model\Indexer\Product\Category\Processor $categoryProcessor,
        \Magento\Catalog\Model\Indexer\Product\Eav\Processor $eavProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceProcessor,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockProcessor,
        \Magento\Framework\App\Helper\Context $context
    ) {
        $this->categoryProcessor = $categoryProcessor;
        $this->eavProcessor = $eavProcessor;
        $this->priceProcessor = $priceProcessor;
        $this->stockProcessor = $stockProcessor;
        parent::__construct($context);
    }

    /**
     * @param $productIds
     * @access public
     */
    public function reindexProductCategory($productIds)
    {
        $this->categoryProcessor->reindexList($productIds);
    }

    /**
     * @param $productIds
     * @access public
     */
    public function reindexProductEav($productIds)
    {
        $this->eavProcessor->reindexList($productIds);
    }

    /**
     * @param $productIds
     * @access public
     */
    public function reindexStock($productIds)
    {
        $this->stockProcessor->reindexList($productIds);
    }

    /**
     * @param reindexPrice $productIds
     * @access public
     */
    public function reindexPrice($productIds)
    {
        $this->priceProcessor->reindexList($productIds);
    }
}
