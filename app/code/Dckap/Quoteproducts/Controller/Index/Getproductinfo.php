<?php
namespace Dckap\Quoteproducts\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;

class Getproductinfo extends \Magento\Framework\App\Action\Action
{
  /**
  * @var \Magento\Framework\View\Result\PageFactory
  */
  protected $resultPageFactory;
  protected $_scopeConfig;
  protected $_objectManager;
  protected $_customersession;


  /**
  * @param \Magento\Framework\App\Action\Context $context
  * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
  */

  public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Framework\View\Result\PageFactory $resultPageFactory,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    CustomerCart $cart,
    \Magento\Customer\Model\Session $customersession,        
    ProductRepositoryInterface $productRepository)
  {        
    parent::__construct($context, $customersession);
    $this->_customersession = $customersession;
    $this->resultPageFactory    = $resultPageFactory;
    $this->_scopeConfig         = $scopeConfig;
    $this->_objectManager       = $context->getObjectManager();
    $this->_productRepository   = $productRepository;  
    $this->_cart                = $cart;
    $this->_eventManager        = $context->getEventManager();           
  }



  public function execute()
  {
    $params = $this->getRequest()->getPost('qry');             
    $model = $this->_objectManager->create('Dckap\Quoteproducts\Model\Quoteproducts');
    $collections = $model->getCollection();
    $collections->getSelect()->join(['quote'=>$collections->getTable('dckap_requestquote')],'main_table.requestquote_id = quote.requestquote_id');
    $collections->getSelect()->join(['product'=>$collections->getTable('catalog_product_entity')],'main_table.product_id = product.entity_id');
    $collections->addFieldToFilter(array('product_name','sku'),array(array('like' => '%'.$params.'%'),array('like' => '%'.$params.'%')));
    $collections->addFieldToFilter('customer_id', $this->_customersession->getCustomer()->getId());
    if(count($collections)){
      foreach($collections as $item)
      {
        $_product = $this->_productRepository->getById($item['product_id']);
        if($item['status'] == 'Approved'){
          $p_qty = "<input type='text' class='qoute_quantity' name='pqty_".$item['request_id']."' id='pqty_".$item['request_id']."' value='".$item['product_qty']."' />";
          $chk = "<input type='checkbox' name='quote_order[]' id='quote_order[]' value='".$item['request_id']."' />";
          $price = '$'.number_format($_product->getPrice(), 2, '.', '');
        } 
        else{
          $chk = $item['status'];
          $p_qty = $item['product_qty'];
          $price = "Pending";
        }
        $pid = "<input type='hidden' Name='qid[]'' id='qid[]'' value='".$item->getRequestId()."'><input type='hidden' name='pid_".$item['request_id']."' id='pid_".$item->getRequestId()."' value='".$item['product_id']."' readonly />";

        echo "<tr><td>".$pid.$item['request_id']."</td><td><a href=".$_product->getProductUrl().">".$_product->getSku()."</a></td><td>".$_product->getName()."</td><td>".$price."</td><td>".$p_qty."</td><td>".date('m/d/y', strtotime($item['create_date']))."</td><td>".$chk."</td></tr>";
      }
    }else{
      echo "<tr><td colspan='7'>No Record found.</td></tr>";
    }
    exit;
  }
}
