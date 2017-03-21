<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Dckap\Topsellingitemscategory\Block\Widget;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Widget\Block\BlockInterface;

/**
 * Catalog Products List widget block
 * Class ProductsList
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Topsellingitemscategory extends \Magento\Catalog\Block\Product\AbstractProduct implements BlockInterface, IdentityInterface
{
    protected $_template = 'widget/Topsellingitemscategory.phtml';
    /**
     * Default value for products count that will be shown
     */
    const DEFAULT_PRODUCTS_COUNT = 10;

    /**
     * Name of request parameter for page number value
     *
     * @deprecated
     */
    const PAGE_VAR_NAME = 'np';

    /**
     * Default value for products per page
     */
    const DEFAULT_PRODUCTS_PER_PAGE = 5;

    /**
     * Default value whether show pager or not
     */
    const DEFAULT_SHOW_PAGER = false;

    /**
     * Instance of pager block
     *
     * @var \Magento\Catalog\Block\Product\Widget\Html\Pager
     */
    protected $pager;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;
  protected $_registry;
    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $catalogProductVisibility;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Rule\Model\Condition\Sql\Builder
     */
    protected $sqlBuilder;
    /**
     * Image helper
     *
     * @var Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;
    /**
     * @var \Magento\CatalogWidget\Model\Rule
     */
    protected $rule;
 protected $_productRepository;
    /**
     * @var \Magento\Widget\Helper\Conditions
     */
    protected $conditionsHelper;
    
       protected $_resourceFactory;
    protected $_categoryFactory;
    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder
     * @param \Magento\CatalogWidget\Model\Rule $rule
     * @param \Magento\Widget\Helper\Conditions $conditionsHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Rule\Model\Condition\Sql\Builder $sqlBuilder,
             \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\CatalogWidget\Model\Rule $rule,
             \Magento\Directory\Model\Currency $currency,
        \Magento\Widget\Helper\Conditions $conditionsHelper,
              \Magento\Catalog\Model\ProductRepository $productRepository,
             \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory,
            \Magento\Framework\Registry $registry,
            
        array $data = []
    ) {
         $this->_resourceFactory = $resourceFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->catalogProductVisibility = $catalogProductVisibility;
        $this->httpContext = $httpContext;
        $this->sqlBuilder = $sqlBuilder;
        $this->rule = $rule;
          $this->_registry = $registry; 
          $this->_currency = $currency; 
          $this->_categoryFactory = $categoryFactory;  
          $this->_productRepository = $productRepository;
        $this->conditionsHelper = $conditionsHelper;
         $this->_imageHelper = $context->getImageHelper();
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addColumnCountLayoutDepend('empty', 6)
            ->addColumnCountLayoutDepend('1column', 5)
            ->addColumnCountLayoutDepend('2columns-left', 4)
            ->addColumnCountLayoutDepend('2columns-right', 4)
            ->addColumnCountLayoutDepend('3columns', 3);

        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => [\Magento\Catalog\Model\Product::CACHE_TAG,
        ], ]);
    }

    /**
     * Get key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $conditions = $this->getData('conditions')
            ? $this->getData('conditions')
            : $this->getData('conditions_encoded');

        return [
            'CATALOG_PRODUCTS_LIST_WIDGET',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            intval($this->getRequest()->getParam($this->getData('page_var_name'), 1)),
            $this->getProductsPerPage(),
            $conditions,
            serialize($this->getRequest()->getParams())
        ];
    }
    
    public function getCurrentCategorytop()
  {   
    return $category = $this->_registry->registry('current_category');
    
//     $category_id = $category->getId();
//     return $category_id;
  }
      public function getCurrentCurrencySymbol()
    {
    return $this->_currency->getCurrencySymbol();
    }
    
     public function getProductUrls($productId)
    {
        $product = $this->_productRepository->getById($productId);
        return $product->getUrlModel()->getUrl($product);;
    }
    
     public function getProductDetails($productId)
    {
        $product = $this->_productRepository->getById($productId);
        return $product;
    }
	/**
     * Image helper Object
     */
 	public function imageHelperObj(){
        return $this->_imageHelper;
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType = null,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['price_id'] = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;

            /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.deffault');

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                $arguments
            );
        }
        return $price;
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        $this->setProductCollection($this->createCollection());
        return parent::_beforeToHtml();
    }
 public function getDirectQuery($product_id)
    {
        $conn = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Framework\App\ResourceConnection');
       $connection = $conn->getConnection(\Magento\Framework\App\ResourceConnection::DEFAULT_CONNECTION);
       $rating_summary = array();

     $rating_summary = $connection->fetchRow('SELECT vote_value_sum FROM  rating_option_vote_aggregated Where entity_pk_value ='.$product_id);
     
    return $rating_summary;
    }
    /**
     * Prepare and return product collection
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function createCollection()
    {
        
        
        $category = $this->getCurrentCategorytop();
        
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $collection = $this->productCollectionFactory->create();
        $collection->setVisibility($this->catalogProductVisibility->getVisibleInCatalogIds());

        $collection = $this->_addProductAttributesAndPrices($collection)
            ->addStoreFilter()
            ->addCategoryFilter($category)
            ->setPageSize($this->getPageSize());
            //->setCurPage($this->getRequest()->getParam($this->getData('page_var_name'), 1));

        $conditions = $this->getConditions();
        $conditions->collectValidatedAttributes($collection);
        $this->sqlBuilder->attachConditionToCollection($collection, $conditions);

        return $collection;
    }
    
     public function getTopBestsellerProduct(){
     $limit = $this->getProductLimit();
       $productdetails = array();
     $nonproductdetails = array();
     $category_id =  $this->getCurrentCategorytop()->getId();
      //echo $category_id;
      
      $connection = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Framework\App\ResourceConnection');
        $conn = $connection->getConnection();
        $sql = "SELECT MAX(DATE_FORMAT(period, '%Y-%m-%d')) AS `period`, SUM(qty_ordered) "
                . "AS `qty_ordered`, `sales_bestsellers_aggregated_yearly`.`product_id`, MAX(product_name) "
                . "AS `product_name`, MAX(product_price) "
                . "AS `product_price` FROM `sales_bestsellers_aggregated_yearly` "
                . "WHERE (sales_bestsellers_aggregated_yearly.product_id IS NOT NULL) "
                . "AND (store_id IN(0)) AND (store_id IN(0)) "
                . "GROUP BY `product_id` "
                . "ORDER BY `qty_ordered` DESC";
        $resourceCollection  = $conn->query($sql);
        
//        return $collection;
//                    $resourceCollection = $this->_resourceFactory->create('Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection');
                  //  echo $resourceCollection->getSelect()->__toString();
    $item_ids=array();
    $items_qty_ordered = array();
   // echo count($resourceCollection); exit;
                foreach ($resourceCollection as $item):  
                    $item_ids[]=$item['product_id'];
                $items_qty_ordered[$item['product_id']]=$item['qty_ordered'];
                endforeach;
             //   echo '<pre>'; 
     //print_r($item_ids); exit;
                $collection = $this->_categoryFactory->create()->load($category_id)->getProductCollection()->addAttributeToSelect('*');
              //  echo count($collection);
$prd_count = 0;

      foreach ($collection as $key=>$product):  
       
                 if(in_array($product->getId(), $item_ids))
                 {
                   //  echo $product->getId(); exit;
                     $productdetails[$product->getId()] = $items_qty_ordered[$product->getId()];
                    // $prd_count++;
                 }else
                 {
                     $nonproductdetails[$product->getId()] = 0;
                     
                 }
                 
//                 if($prd_count == 3)
//                 {
//                     break;
//                 }
//                 
                endforeach;
           //echo count($nonproductdetails);
       arsort($productdetails);
//      print_r($nonproductdetails); 
    //  echo '---------------';
                
                if(count($productdetails) < 5 && count($productdetails) != 0 && count($nonproductdetails) != 0)
                {
                  //  echo count($productdetails);
                 $n = 5 - count($productdetails);
                // print_r($n);exit;
                $nonproductdetails = array_slice($nonproductdetails, 0, $n, true);
               //  print_r($nonproductdetails); exit;
//                    for($j =0;$j< $n ;$j++) {
//                       // echo $j;
//                         $productdetails[] = $nonproductdetails[$j];
//                    }
                }
                  // echo count($productdetails);
                 $productdetails = array_slice($productdetails, 0, 5, true);
                 //print_r($productdetails);                 exit();
    return ($productdetails);
   }
   
    public function getProductLimit() {
		
        return 5;
    }

    /**
     * @return \Magento\Rule\Model\Condition\Combine
     */
    protected function getConditions()
    {
        $conditions = $this->getData('conditions_encoded')
            ? $this->getData('conditions_encoded')
            : $this->getData('conditions');

        if ($conditions) {
            $conditions = $this->conditionsHelper->decode($conditions);
        }

        $this->rule->loadPost(['conditions' => $conditions]);
        return $this->rule->getConditions();
    }

    public function getProducts()
    {
        return $this->getProductCollection();
    }

    /**
     * Retrieve how many products should be displayed
     *
     * @return int
     */
    public function getProductsCount()
    {
        if ($this->hasData('productcount')) {
            return $this->getData('productcount');
        }

        if (null === $this->getData('productcount')) {
            $this->setData('productcount', self::DEFAULT_PRODUCTS_COUNT);
        }

        return $this->getData('productcount');
    }

    /**
     * Retrieve how many products should be displayed
     *
     * @return int
     */
    public function getProductsPerPage()
    {
        if (!$this->hasData('products_per_page')) {
            $this->setData('products_per_page', self::DEFAULT_PRODUCTS_PER_PAGE);
        }
        return $this->getData('products_per_page');
    }

    /**
     * Return flag whether pager need to be shown or not
     *
     * @return bool
     */
    public function showPager()
    {
        if (!$this->hasData('show_pager')) {
            $this->setData('show_pager', self::DEFAULT_SHOW_PAGER);
        }
        return (bool)$this->getData('show_pager');
    }

    /**
     * Retrieve how many products should be displayed on page
     *
     * @return int
     */
    protected function getPageSize()
    {
        return 5;
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        if ($this->showPager() && $this->getProductCollection()->getSize() > $this->getProductsPerPage()) {
            if (!$this->pager) {
                $this->pager = $this->getLayout()->createBlock(
                    'Magento\Catalog\Block\Product\Widget\Html\Pager',
                    'widget.products.list.pager'
                );

                $this->pager->setUseContainer(true)
                    ->setShowAmounts(true)
                    ->setShowPerPage(false)
                    ->setPageVarName($this->getData('page_var_name'))
                    ->setLimit($this->getProductsPerPage())
                    ->setTotalLimit($this->getProductsCount())
                    ->setCollection($this->getProductCollection());
            }
            if ($this->pager instanceof \Magento\Framework\View\Element\AbstractBlock) {
                return $this->pager->toHtml();
            }
        }
        return '';
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        if ($this->getProductCollection()) {
            foreach ($this->getProductCollection() as $product) {
                if ($product instanceof IdentityInterface) {
                    $identities = array_merge($identities, $product->getIdentities());
                }
            }
        }

        return $identities ?: [\Magento\Catalog\Model\Product::CACHE_TAG];
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
}
