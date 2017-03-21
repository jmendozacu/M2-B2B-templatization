<?php
namespace Dckap\Quickorder\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;

class Addproduct extends \Magento\Framework\App\Action\Action
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



    public function addConfProduct($cparams) {
        /*echo '<pre>';print_r($cparams);echo '</pre>';
        die('Data ends here @ '.__LINE__.'==>'.__FILE__);*/
        $errorReport = array();
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        foreach ($cparams['configurable'] as $cpkey => $cpvalues) {
            //$this->_cart = $this->_objectManager->get('Magento\Checkout\Model\Cart');
            $prodParams = array();
            $prodParams['product']          = $cpvalues['product'];                       
            $attributeParts                 = explode(',',$cpvalues['superAttribute']);
            $prodParams['super_attribute']  = array();
            $attParts = ''; 
            foreach ($attributeParts as $apkey => $apvalue) {
                $attParts = explode('=', $apvalue);
                $prodParams['super_attribute'][$attParts[0]] = $attParts[1];
            }
            //echo '<pre>';print_r($prodParams);echo '</pre>';
            if($cpvalues['qty'] <= 0)
                continue;
            try {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
                );
                $pqty = $filter->filter($cpvalues['qty']);
                
                $product = '';    
                //echo '=='.__LINE__.'=productid=>'. $cpvalues['product'].'<br/>';
                if ($cpvalues['product']) {                        
                    try {
                        $product = $this->_productRepository->getById($cpvalues['product'], false, $storeId);
                    } catch (NoSuchEntityException $e) {
                        array_push($errorReport,$e->getMessage());
                        //echo '=NoSuchEntityException=>'.$e->getMessage();
                        continue;       
                    }
                }
                //echo '=='.__LINE__.'==>'. $product->getName().'<br/>';
                $prodParams['qty']      = $pqty; 
                $prodParams['related_product'] = $prodParams['selected_configurable_option'] = '';
                //echo '<pre>$prodParams';print_r($prodParams);echo '</pre>';
                //echo '=='.__LINE__.'=b4addProduct=>'. $product->getId().'==='.$product->getName().'<br/>';
                $this->_cart->addProduct($product, $prodParams);
                $this->_cart->save();

                $this->_eventManager->dispatch(
                    'checkout_cart_add_product_complete',
                    ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
                );
                //echo '=='.__LINE__.'==>'. $this->_cart->getQuote()->getHasError().'==='.$product->getName().'<br/>';
                if ($this->_cart->getQuote()->getHasError()) {  
                    array_push($errorReport, $product->getName());
                    continue;
                    //$errorReport[$product->getName()] = 'Some problem while adding the product to cart.';
                }                           
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                //$errorReport[$product->getName()] = $e->getMessage();
                array_push($errorReport,$e->getMessage().' ('.$product->getName().')');
                continue;
            } catch (\Exception $e) {
                //$errorReport[$product->getName()] = $e->getMessage();           
                array_push($errorReport,$e->getMessage().' ('.$product->getName().')');
                continue;
            }                        
        }   
        if (count($errorReport) == 0) {
            echo json_encode(
                                array('status'=>'success',
                                      'message'=>'Products were added to cart successfully.')
                            );
        } else {
            echo json_encode(
                                array('status'=>'failure',
                                      'message'=> 'Problem while adding these products to cart : '. implode(',',$errorReport) )
                            );
        }         
    }
    
  
    public function execute()
    {        
        if ($this->getRequest()->isAjax()) {
            $errorReport = array();
            $params = $this->getRequest()->getParams();    
            if(isset($params['configurable']) && count($params['configurable']) > 0) {
                $this->addConfProduct($params);
            }
            else if(isset($params['simple']) && $params['simple'] != '') {    
                $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
                $productIds = $productQty = array();
                
                if(isset($params['simple']['psku']) && $params['simple']['psku'] != '') {
                    $skuQuantitySeparator = trim($this->_scopeConfig->getValue('quickorder_section/quickorder_group_general/sku_quantityseparator', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                    if($skuQuantitySeparator == '')
                        $skuQuantitySeparator = '|#|';
                    $productSKUList = explode(',',$params['simple']['psku']);
                    if(count($productSKUList) > 0) {
                        foreach ($productSKUList as $key => $skuwithqty) {
                            $valueArray = explode($skuQuantitySeparator,$skuwithqty);
                            if($valueArray[0] != '') {
                                try {
                                    $product = $this->_productRepository->get($valueArray[0], false, $storeId); 
                                    //echo '<pre>';print_r($valueArray);echo '</pre>';
                                    if($product->getId() > 0 && $valueArray[1] > 0) {
                                        array_push($productIds,$product->getId());
                                        array_push($productQty,trim($valueArray[1]));
                                    }    
                                } catch (NoSuchEntityException $e) {
                                    array_push($errorReport,$e->getMessage().' ('.$valueArray[0].')');
                                    continue;       
                                }
                            } else {
                                continue;
                            }                                                            
                        }
                    }
                } 
                elseif (isset($params['simple']['pids']) && $params['simple']['pids'] != '') {
                    $productIds = explode(',',$params['simple']['pids']);
                    $productQty = explode(',',$params['simple']['pqty']);    
                }
                /*echo '<pre>';print_r($productIds);echo '</pre>';
                echo '<pre>';print_r($productQty);echo '</pre>';*/                
                unset($params['simple']);
                foreach ($productIds as $pkey => $productId) {
                    $pqty = isset($productQty[$pkey])?$productQty[$pkey]:0;
                    if($pqty <= 0)
                        continue;
                    try {
                        $filter = new \Zend_Filter_LocalizedToNormalized(
                            ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
                        );
                        $pqty = $filter->filter($pqty);                        
                        $product = '';    
                        if ($productId) {                        
                            try {
                                //$product = $this->_productRepository->getById($productId, false, $storeId);
                                //echo '=='.__LINE__.'=$productId=>'. $productId.'<br/>';
                                $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
                                //echo '<pre>';print_r($product->getData());echo '</pre>';
                            } catch (NoSuchEntityException $e) {
                                //array_push($errorReport,$e->getMessage().'('.$valueArray[0].')');
                                array_push($errorReport,$e->getMessage());
                                continue;       
                            }
                        }
                        
                        if(trim($product->getTypeId()) == 'configurable') {
                            array_push($errorReport,"Configurable product (".$product->getName().") cannot be added here.");
                            break;
                        }

                        $params['qty'] = $pqty;
                        $params['product'] = $productId;
                        $params['related_product'] = $params['selected_configurable_option'] = '';
                        $this->_cart->addProduct($product, $params);                        
                        $this->_eventManager->dispatch(
                            'checkout_cart_add_product_complete',
                            ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
                        );
                        if ($this->_cart->getQuote()->getHasError()) {  
                            array_push($errorReport,'Some problem while adding the product ('.$product->getName().')');
                            continue;
                        }           
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        array_push($errorReport,$e->getMessage().' ('.$product->getName().')');
                        continue;
                    } catch (\Exception $e) {
                        array_push($errorReport,$e->getMessage().' ('.$product->getName().')');
                        continue;
                    }
                }
                
                try {
                    $this->_cart->save();
                } catch (\Exception $e) {
                    array_push($errorReport,$e->getMessage());
                }


                if (count($errorReport) == 0 && is_array($productIds) && count($productIds) > 0) {
                    echo json_encode(
                                        array('status'=>'success',
                                              'message'=>'Products were added to cart successfully')
                                    );
                } 
                else if(count($errorReport) == 0 && is_array($productIds) && count($productIds) == 0) {
                    echo json_encode(
                                        array('status'=>'failure',
                                              'message'=>'Problem while adding products to cart')
                                    );
                }
                else if(count($errorReport) > 0) {
                    echo json_encode(
                                        array('status'=>'failure',
                                              'message'=> implode(',',$errorReport) )
                                    );
                }
            }     
            die;
        }
        else {
            $siteBaseUrl = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getBaseUrl();
            $this->_redirect($siteBaseUrl);        
        }
    }
}
