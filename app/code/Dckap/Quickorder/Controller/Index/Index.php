<?php
namespace Dckap\Quickorder\Controller\Index;
class Index extends \Magento\Framework\App\Action\Action {
    protected $resultPageFactory;
    protected $scopeConfig;
    protected $_objectManager;
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->resultPageFactory    = $resultPageFactory;
        $this->scopeConfig          = $scopeConfig;
        $this->_objectManager       = $context->getObjectManager();
        $this->_storeManager        = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        parent::__construct($context);        
    }

    public function execute() {    
        $query = $this->getRequest()->getParam('query');
        $qoProductCollection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $qoProductCollection->addAttributeToSelect(array('name','price','sku','id','type_id','small_image'));           
        $qoProductCollection->addAttributeToFilter('visibility',array('neq'=> 1));
        $qoProductCollection->addAttributeToFilter(array(array('attribute'=> 'name','like'=> '%'.$query.'%'),array('attribute'=> 'sku','like'=> '%'.$query.'%')));
        $qoProductCollection->getSelect()->where(" type_id in ('simple','configurable','downloadable','virtual') ");              
        //$qoProductCollection->getSelect()->limit(10,0);
        $qoProductCollection->load();
        $productData = array();
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product';
        foreach ($qoProductCollection as $productObj) {
           
          
            $pimage = (trim($productObj->getSmallImage()) != '' && trim($productObj->getSmallImage()) != 'no_selection')?$mediaUrl.$productObj->getSmallImage():'';            
                  
            //echo '=='.__LINE__.'==>'. trim($productObj->getSmallImage()) .'<br/>';
            $productData[] = array('label' => $productObj->getName(),'title' => $productObj->getName(),'value' => $productObj->getId(),'sku'   => $productObj->getSKU(),'price' => $productObj->getPrice(),'ptype'  => $productObj->getTypeId(),'pimage' => $pimage);
        }
        if(is_array($productData) && count($productData) == 0) {
            $productData[] = array('label' => 'No matches found','title' => 'No matches found','value' => 'no-matches');   
        }
        die(json_encode($productData));        
        //$pTitle = $productObj->getName().' ('.$productObj->getTypeId().')';        
    }
}

