<?php
namespace Dckap\Quoteproducts\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;

class Pdfdownload extends \Magento\Framework\App\Action\Action
{
  /**
  * @var \Magento\Framework\View\Result\PageFactory
  */
  protected $resultPageFactory;
  protected $_scopeConfig;
  protected $_objectManager;
  protected $_customersession;
  protected $currency;


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
    \Magento\Directory\Model\Currency $currency,       
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
    $this->currency             = $currency;           
  }

  public function execute()
  {      
    $values = $this->getRequest()->getParams('qids');
    $model = $this->_objectManager->create('Dckap\Quoteproducts\Model\Quoteproducts');
    $collections = $model->getCollection();
    $collections->addFieldToFilter('request_id', array('in'=>$values));
    $collections->getSelect()->join(['quote'=>$collections->getTable('dckap_requestquote')],'main_table.requestquote_id = quote.requestquote_id');
    $collections->addFieldToFilter('customer_id', $this->_customersession->getCustomer()->getId());
    if(count($collections)){
      foreach($collections as $item)
      {
        $_product = $this->_productRepository->getById($item['product_id']);
        if($item->getStatus() == 'Approved')
          $price = $this->getCurrencySymbol().number_format($_product->getPrice(),2);
        else
          $price = "Pending";
        $output[] = array($item['request_id'],$_product->getSku(),$_product->getName(),$price,$item['product_qty'],date('m/d/y', strtotime($item['create_date']))); 
      }
      print_r(json_encode($output));
      exit;
    }
  }
  
  public function getCurrencySymbol()
  {
    return $this->currency->getCurrencySymbol();
  }
}