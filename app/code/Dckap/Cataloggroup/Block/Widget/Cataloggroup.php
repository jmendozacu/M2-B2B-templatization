<?php
namespace Dckap\Cataloggroup\Block\Widget;

use Magento\Widget\Block\BlockInterface;

class Cataloggroup extends \Magento\Framework\View\Element\Template implements BlockInterface 
{ 

    protected $_template = 'widget/Cataloggroup.phtml';

    protected $_categoryHelper; 

    protected $_categoryFactory;

    protected $categoryFlatConfig; 

    protected $topMenu; 

    protected $_categoryCollection; 
    /**
    * Default value for products count that will be shown
    */
    const DEFAULT_CATEGORY_IDS = 0;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
        \Magento\Theme\Block\Html\Topmenu $topMenu,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection )
    {

        $this->_categoryHelper = $categoryHelper;  
        $this->_categoryFactory = $categoryFactory;  
        $this->_categoryCollection = $categoryCollection;
        parent::__construct($context);
    }
    /**
    * Return categories helper
    */
    public function getCategoryHelper()
    {
        return $this->_categoryHelper;
    }
    public function getCategory($cat_id) 
    {

        $category = $this->_categoryFactory->create();
        $category->load($cat_id);    
        return $category;
    }
    public function getCategoryCollection()
    {
        $collection = $this->_categoryCollection->create()
        ->addAttributeToSelect('name')
        ->addAttributeToSelect('image_url')
        ->setPageSize(2);
        return $collection;
    }
    /**
    * Retrieve how many products should be displayed
    *
    * @return int
    */
    public function getCategoryIds()
    {
        
        if ($this->hasData('categoryids')) {
            return $this->getData('categoryids');
        }

        if (null === $this->getData('categoryids')) {
            $this->setData('categoryids', self::DEFAULT_PRODUCTS_COUNT);
        }

        return $this->getData('categoryids');
    }
    public function getStoreCategories($sorted = false, $asCollection = false, $toLoad = true)
    {
        return $this->_categoryHelper->getStoreCategories($sorted , $asCollection, $toLoad);
    }
    /**
    * Get value of widgets' title parameter
    *
    * @return mixed|string
    */
    public function getTitle()
    {
        return $this->getData('title');
    }
    /**
    * Retrieve child store categories
    *
    */ 
    public function getChildCategories($category)
    {
        if ( $category->getUseFlatResource()) {
            $subcategories = (array)$category->getChildrenNodes();
        } else {
            $subcategories = $category->getChildren();
        }

        return $subcategories;
    }
    public function getCategoryImageUrl($categoryId)
    {
        $this->_category = $this->_categoryFactory->create();
        $this->_category->load($categoryId);   

        if($this->_category->getImageUrl()) {            
            return $this->_category->getImageUrl();
        } else {
            return $this->getBaseUrl().'pub/media/dckap/no_product.png';
        }
    }
}