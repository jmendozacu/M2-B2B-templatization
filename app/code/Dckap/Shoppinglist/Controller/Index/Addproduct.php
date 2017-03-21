<?php
namespace Dckap\Shoppinglist\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\Session;

class Addproduct extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    protected $_scopeConfig;
	protected $session;


    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CustomerCart $cart,
        ProductRepositoryInterface $productRepository,
		Session $customerSession)
    {        
        parent::__construct($context);
        $this->resultPageFactory    = $resultPageFactory;
        $this->_scopeConfig         = $scopeConfig;
        $this->_objectManager       = $context->getObjectManager();
        $this->_productRepository   = $productRepository;  
        $this->_cart                = $cart;
        $this->_eventManager        = $context->getEventManager();
		 $this->session 			= $customerSession;             
    }
    public function execute()
    {       
        if ($this->session->isLoggedIn()) {
		  $connection = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Framework\App\ResourceConnection')->getConnection();
		    $errorReport = array();
            $productParams = $this->getRequest()->getParam("bulk"); 
            $shopinglistid = $this->getRequest()->getParam("shoppinglist_id");
           
                $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
                
                for ($i=0; $i<count($productParams); $i++) {
                    $data = $connection->fetchRow("select * from shopping_list_item where shopping_list_id=".$shopinglistid." and item_id=".$productParams[$i]);
					$params = array();
					$productId = $productParams[$i];
					$qty = $data['qty'];
					try {
                            try {
                                $product = $this->_productRepository->getById($productId, false, $storeId);
                            } catch (NoSuchEntityException $e) {
                                //array_push($errorReport,$e->getMessage().'('.$valueArray[0].')');
                                array_push($errorReport,$e->getMessage());
                                continue;       
                            }
                        
                        $params['qty'] = $qty;
                        $params['product'] = $productId;
                        $params['related_product'] = $params['selected_configurable_option'] = '';
						//print_r($params); exit;
                        $this->_cart->addProduct($product, $params);
                        //$this->_cart->save();
                        $this->_eventManager->dispatch(
                            'checkout_cart_add_product_complete',
                            ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
                        );
                        if ($this->_cart->getQuote()->getHasError()) {  
                            array_push($errorReport,'Some problem while adding the product ('.$product->getName().')');
                            continue;
                        } 
						}          
                    catch (\Magento\Framework\Exception\LocalizedException $e) {
                        array_push($errorReport,$e->getMessage().' ('.$product->getName().')');
                        continue;
                    } 
					
					}
					
				 try {
                    $this->_cart->save();
                } catch (\Exception $e) {
                    array_push($errorReport,$e->getMessage());
                    //continue;       
                }
				
                if (count($errorReport) == 0) {
                   /* echo json_encode(
                                        array('status'=>'success',
                                              'message'=>'Products were added to cart successfully')
                                    );*/
									$this->messageManager->addSuccess( __(' Products were added to cart successfully.') );
                } 
                else if(count($errorReport) > 0) {
                    /*echo json_encode(
                                        array('status'=>'failure',
                                              'message'=> implode(',',$errorReport) )
                                    );*/
								$this->messageManager->addError( __(implode(',',$errorReport)) );
                }
				//die();
				$resultRedirect = $this->resultRedirectFactory->create();
				$resultRedirect->setPath('shoppinglist/index/index/');
				return $resultRedirect;
            }     
        else {
            $resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setPath('customer/account/login/');
			return $resultRedirect;       
        }
    }
}
