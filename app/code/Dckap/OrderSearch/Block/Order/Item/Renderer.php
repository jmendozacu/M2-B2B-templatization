<?php

namespace Dckap\OrderSearch\Block\Order\Item;

use Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer as DefaultRenderer;

class Renderer extends DefaultRenderer
{
    /**
     * Magento string lib
     *
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var \Magento\Catalog\Model\Product\OptionFactory
     */
    protected $_productOptionFactory;

    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    protected $imageBuilder;

    protected $_productloader;  


    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        array $data = []
        ) {
        $this->imageBuilder = $imageBuilder;
        $this->_productloader = $_productloader;
        parent::__construct($context, $string, $productOptionFactory, $data);
    }

    /**
     * Get item product
     *
     * @return \Magento\Catalog\Model\Product
     * @codeCoverageIgnore
     */
    public function getProduct()
    {
        return $this->getItem()->getProduct();
    }

    /**
     * Identify the product from which thumbnail should be taken.
     *
     * @return \Magento\Catalog\Model\Product
     * @codeCoverageIgnore
     */
    public function getProductForThumbnail()
    {
        return $this->getProduct();
    }

    /**
     * Retrieve product image
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $imageId
     * @param array $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->imageBuilder->setProduct($product)
        ->setImageId($imageId)
        ->setAttributes($attributes)
        ->create();
    }
    public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }
}