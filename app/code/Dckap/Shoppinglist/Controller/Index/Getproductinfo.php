<?php
namespace Dckap\Shoppinglist\Controller\Index;

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


    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository)
    {        
        parent::__construct($context);
        $this->resultPageFactory    = $resultPageFactory;
        $this->_scopeConfig         = $scopeConfig;
        $this->_objectManager       = $context->getObjectManager();
        $this->_productRepository   = $productRepository;  
        $this->_cart                = $cart;
        $this->_eventManager        = $context->getEventManager();              
    }

    
  
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $params = $this->getRequest()->getParams();             
            $productType    = $params['ptype'];
            $productId      = $params['pid'];
            $configurableProduct = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
            $confProdTypeObject = $this->_objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable');
            $usedProducts       = $confProdTypeObject->getUsedProducts($configurableProduct);
            $confAttributesData = $this->_objectManager->create('Magento\ConfigurableProduct\Model\ConfigurableAttributeData')->getAttributesData($configurableProduct,array());            
            $configurableAttributes['data'] = $confAttributeCodes = array();
            if(isset($confAttributesData['attributes']) && count($confAttributesData['attributes']) > 0) {
                foreach ($confAttributesData['attributes'] as $attcode => $attValues) {
                    if(!isset($configurableAttributes['data'][$attValues['code']])) {
                        $configurableAttributes['data'][$attValues['code'].'_'.$attValues['id']] = array();
                        $configurableAttributes['data'][$attValues['code'].'_'.$attValues['id']]['attrLabel'] = $attValues['label'];
                        array_push($confAttributeCodes,$attValues['code']);
                    }
                    foreach ($attValues['options'] as $optkey => $optValues) {
                        $configurableAttributes['data'][$attValues['code'].'_'.$attValues['id']][$optValues['id']] = $optValues['label'];
                    }
                }
            }
            $configurableAttributes['mapping'] = array();
            foreach ($usedProducts as $up) {
                $attKeys = array();              
                foreach ($confAttributeCodes as $cac) {                   
                   $methodName = "get{$cac}";
                   $attKeys[] = $cac."_".$up->$methodName();
                }
                $configurableAttributes['mapping']{implode('-',$attKeys)}['product']    = $up->getEntityId();
                $configurableAttributes['mapping']{implode('-',$attKeys)}['price']      = $up->getPrice();
            }
            if(isset($configurableAttributes['data'])) {
                die(json_encode(array('status'=>'success','cdata'=> $configurableAttributes)));
            } else {
                die(json_encode(array('status'=>'failure')));
            }
            //die;
        }
        else {
            $siteBaseUrl = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl();
            $this->_redirect($siteBaseUrl);        
        }
    }
}
