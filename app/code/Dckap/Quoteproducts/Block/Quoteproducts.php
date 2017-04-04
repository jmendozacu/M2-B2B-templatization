<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2015 Amasty (http://www.amasty.com)
 * @package Amasty_HelloWorld
 */
namespace Dckap\Quoteproducts\Block;

class Quoteproducts extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    protected $_customersession;
    protected $_categoryFactory;
    protected $_productRepository;
    protected $currency;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customersession,
        \Magento\Directory\Model\Currency $currency,
        array $data = []
        ) {
        $this->_objectManager = $objectManager;
        $this->_customersession = $customersession;
        $this->_categoryFactory = $categoryFactory;  
        $this->_productRepository = $productRepository;
        $this->currency = $currency;
        parent::__construct($context, $data, $customersession);

    }
    protected function _prepareLayout()
    {

        parent::_prepareLayout();
        
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'quote.product.pager'
                )->setAvailableLimit(array(10=>10))
            ->setShowPerPage(true)->setCollection(
                $this->getCollection()
                );
            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    
    public function getCollection()
    {
        //get values of current page
        $page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;

        //get values of current limit
        $pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest
        ()->getParam('limit') : 10;
        $model = $this->_objectManager->create('Dckap\Quoteproducts\Model\Quoteproducts');
        $collection = $model->getCollection()->setOrder('request_id','DESC');
        $collection->getSelect()->join(['quote'=>$collection->getTable('dckap_requestquote')],'main_table.requestquote_id = quote.requestquote_id');
        $collection->addFieldToFilter('customer_id', $this->_customersession->getCustomer()->getId());
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        return $collection;
    }

    public function getProductById($id)
    {
        return $this->_productRepository->getById($id);
    }

    public function getCategory($categoryId) 
    {
        $this->_category = $this->_categoryFactory->create();
        $this->_category->load($categoryId);        
        return $this->_category;
    }
    public function getCurrencySymbol()
    {
        return $this->currency->getCurrencySymbol();
    }
}