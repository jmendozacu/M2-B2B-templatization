<?php
namespace Dckap\Categorywidget\Block\Widget;

class Categorywidget extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
  const XML_SHORT_NAME_ENABLE = 'catalog/custom_attribute_shortname/use_short_name';

  protected $_productCollectionFactory;
  protected $_template = '';
  protected $_categoryFactory;
  protected $_category;
  public $_storeManager;
  protected $_registry;
  protected $_productRepository;
  protected $_resourceFactory;
  protected $_scopeConfig;
  
  public function __construct(
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Backend\Block\Template\Context $context,   
    \Magento\Catalog\Model\CategoryFactory $categoryFactory,
    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
    \Magento\Catalog\Model\ProductRepository $productRepository,
    \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory,
    \Magento\Framework\Registry $registry,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    array $data = []
    )
  {
    $this->_resourceFactory = $resourceFactory;
    $this->_storeManager=$storeManager;
    $this->_productRepository = $productRepository;
    $this->_productCollectionFactory = $productCollectionFactory;
    $this->_categoryFactory = $categoryFactory;  
    $this->_registry = $registry; 
    $this->_scopeConfig = $scopeConfig;

    if(count($this->getCategoryCount()) == 3  )
    {
      $this->setTemplate('widget/3-layout-categorywidget.phtml');
    }  else if(count($this->getCategoryCount()) == 2  )
    {
      $this->setTemplate('widget/2-layout-categorywidget.phtml');
    }  else if(count($this->getCategoryCount()) == 4  )
    {
      $this->setTemplate('widget/4-layout-categorywidget.phtml');
    }else
    {
      $this->setTemplate( 'widget/categorywidget.phtml');
    }

    parent::__construct($context, $data);
  }
  public function getNoCategoryUrl()
  {
    return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'template/no_product.png';
  }
  public function getProductDetails($productId)
  {
    return $this->_productRepository->getById($productId);
  }
  public function getCategoryCount()
  {
    return  $this->getCategory()->getChildrenCategories();
  }
  public function getConfigData()
  {
    return $this->_scopeConfig->getValue(self::XML_SHORT_NAME_ENABLE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
  }

  public function getBestsellerProduct($category_id){
    $limit = $this->getProductLimit();
    $productdetails = array();
    $nonproductdetails = array();
    
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

    $item_ids=array();
    $items_qty_ordered = array();
    foreach ($resourceCollection as $item):  
      $item_ids[]=$item['product_id'];
    $items_qty_ordered[$item['product_id']]=$item['qty_ordered'];
    endforeach;
    
    $collection = $this->_categoryFactory->create()->load($category_id)->getProductCollection()->addAttributeToSelect('*');
    $prd_count = 0;

    foreach ($collection as $key=>$product):  

      if(in_array($product->getId(), $item_ids)){
        $productdetails[$product->getId()] = $items_qty_ordered[$product->getId()];
      }else{
        $nonproductdetails[$product->getId()] = 0;
      }
      endforeach;
      arsort($productdetails);

      if(count($productdetails) < 3 && count($productdetails) != 0 && count($nonproductdetails) != 0){
        $noproddetails = count($nonproductdetails);
        $needed = 3- count($productdetails);
        $noproddetails;
        if($noproddetails != 0){
        if($noproddetails <= $needed){ //having lesser than needed
          $n = $noproddetails;
          $nonproductdetails = array_slice($nonproductdetails, 0, $noproddetails, true);
        } else  if($noproddetails > $needed) { //having greater than needed
          $n = $needed;
          $nonproductdetails = array_slice($nonproductdetails, 0, $needed, true);
        }
        $productdetails = $productdetails + $nonproductdetails;
      }
    }
    $productdetails = array_slice($productdetails, 0, 3, true);
    return ($productdetails);
  }
  protected function getCurrentCategory()
  {   
    $category = $this->_registry->registry('current_category');
    return $category;
  }

  /**
  * Get category object
  *
  * @return \Magento\Catalog\Model\Category
  */
  public function getCategory() 
  {
    $cat_id =$this->getCurrentCategory();  
    return $cat_id;
  }

  public function canShowCategory()
  {
    $category = $this->getCategory();
    $parentCategories = $category->getChildrenCategories();
    return count($parentCategories);
  }

  public function getCategoryImageUrl($categoryId)
  {
    $this->_category = $this->_categoryFactory->create();
    $this->_category->load($categoryId);    
    if($this->_category->getImageUrl()){
      return $this->_category->getImageUrl();
    } else {
      return ;
    }
  }
}
?>
