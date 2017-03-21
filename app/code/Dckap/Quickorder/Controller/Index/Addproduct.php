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
                array_push($errorReport,$e->getMessage());
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
    
    protected function _initProduct($productIdentifier,$type)
    {
        if ($productIdentifier) {
            $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
            try {
                if($type == 'sku')
                    return $this->_productRepository->get($productIdentifier, false, $storeId);
                else     
                    return $this->_productRepository->getById($productIdentifier, false, $storeId);            
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
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
                $productIds = $productQty = array();       
                if(isset($params['simple']['psku']) && $params['simple']['psku'] != '') {
                    $skuQuantitySeparator = trim($this->_scopeConfig->getValue('quickorder_section/quickorder_group_general/sku_quantityseparator', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
                    $skuQuantitySeparator = ($skuQuantitySeparator == '')?'|#|':$skuQuantitySeparator;
                    $productSKUList = explode(',',trim($params['simple']['psku']));                    
                    if(count($productSKUList) > 0) {
                        $productAddedToCart = false;
                        foreach ($productSKUList as $key => $skuwithqty) {
                            $cartAddParams = array();
                            $valueArray = explode($skuQuantitySeparator,$skuwithqty);
                            //echo '<pre>$valueArray';print_r($valueArray);echo '</pre>';
                            if(trim($valueArray[0]) != '' && $valueArray[1] > 0) {
                                try {
                                    $filter = new \Zend_Filter_LocalizedToNormalized(
                                        ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
                                    );
                                    $cartAddParams['qty'] = $filter->filter(trim($valueArray[1]));
                                    $product = $this->_initProduct($valueArray[0],'sku');
                                    if(!$product) 
                                        continue;
                                    $cartAddParams['product'] = $product->getId();
                                    $cartAddParams['related_product'] = '';
                                    $cartAddParams['selected_configurable_option'] = '';
                                    $this->_cart->addProduct($product, $cartAddParams);
                                    $productAddedToCart = true;
                                    $this->_eventManager->dispatch('checkout_cart_add_product_complete',
                                        ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
                                    );
                                }                                
                                catch (\Magento\Framework\Exception\LocalizedException $e) {
                                    array_push($errorReport,$e->getMessage().' ('.$product->getName().')');
                                    continue;
                                } 
                                catch (\Exception $e) {
                                    array_push($errorReport,$e->getMessage());
                                    continue;
                                }
                            }         
                        }
                        if($productAddedToCart == true)
                            $this->_cart->save();
                    }
                } 
                elseif (isset($params['simple']['pids']) && $params['simple']['pids'] != '') {
                    $productIds = explode(',',$params['simple']['pids']);
                    $productQty = explode(',',$params['simple']['pqty']);    
                    /*echo '<pre>';print_r($productIds);echo '</pre>';
                    echo '<pre>';print_r($productQty);echo '</pre>';*/
                    $productAddedToCart = false;
                    foreach ($productIds as $pkey => $productId) {
                        $cartAddParams = array();
                        $pqty = isset($productQty[$pkey])?$productQty[$pkey]:0;
                        try {
                            $filter = new \Zend_Filter_LocalizedToNormalized(
                                ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
                            );
                            $cartAddParams['qty'] = $filter->filter(trim($pqty));
                            $product = $this->_initProduct($productId,'id');
                            if(!$product) 
                                continue;
                            $cartAddParams['product'] = $productId;
                            $cartAddParams['related_product'] = '';
                            $cartAddParams['selected_configurable_option'] = '';
                            $this->_cart->addProduct($product, $cartAddParams);
                            $productAddedToCart = true;
                            $this->_eventManager->dispatch('checkout_cart_add_product_complete',
                                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
                            );
                        }                                
                        catch (\Magento\Framework\Exception\LocalizedException $e) {
                            array_push($errorReport,$e->getMessage().' ('.$product->getName().')');
                            continue;
                        } 
                        catch (\Exception $e) {
                            array_push($errorReport,$e->getMessage());
                            continue;
                        }
                    }
                    if($productAddedToCart == true)
                        $this->_cart->save();
                }

                if (count($errorReport) == 0) {
                    echo json_encode(
                                        array('status'=>'success',
                                              'message'=>'Products were added to cart successfully')
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
