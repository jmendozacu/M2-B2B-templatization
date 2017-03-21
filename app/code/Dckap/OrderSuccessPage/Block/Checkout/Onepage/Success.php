<?php
namespace Dckap\OrderSuccessPage\Block\Checkout\Onepage;

use \Magento\Framework\View\Element\Template;

class Success extends \Magento\Checkout\Block\Onepage\Success
{ 
    protected $_countryFactoryCollection;
    protected $_productRepository;
    protected $_productImageHelper;
    protected $_salesOrderCollection;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Directory\Model\CountryFactory $countryFactoryCollection,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Helper\Image $productImageHelper,
        \Magento\Sales\Model\Order $salesOrderCollection,
        array $data = []
    ) {
        
        $this->_countryFactoryCollection = $countryFactoryCollection;
        $this->_productRepository = $productRepository;
        $this->_productImageHelper = $productImageHelper;
        $this->_salesOrderCollection = $salesOrderCollection;
        parent::__construct($context, $checkoutSession,$orderConfig,$httpContext,$data);
    }
    public function getOrder() 
    {
        return $this->_checkoutSession->getLastRealOrder();
    }
    public function getds()
    {
        $IncrementId  = $this->_checkoutSession->getLastRealOrder()->getIncrementId();
        $order_information = $this->_salesOrderCollection->loadByIncrementId($IncrementId);
        $order = $order_information;
        return $order;
    }
    public function getCountryFactory()
    {
        return $this->_countryFactoryCollection;
    }
    public function getProductRepository($itemId)
    {
        return $this->_productRepository->getById($itemId);
    }
    public function getProductImageHelper()
    {
        return $this->_productImageHelper;
    }
}