<?php
namespace Dckap\ApplyForCredit\Block;

use Magento\Framework\Mail\MessageInterface;

class Credit extends \Magento\Framework\View\Element\Template
{	
	
	protected $_registry;
	public $_storeManager;
	protected  $_resource;
	protected $credits;
	protected $customerSession;
	protected $_currency;
	protected $customer;
	protected $urlModel;
	protected $_transportBuilder;
	protected $inlineTranslation;
	protected $scopeConfig;
	protected $message;
	protected $logger;
	
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\UrlFactory $urlFactory,		
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Directory\Model\Currency $currency,  
		\Dckap\ApplyForCredit\Model\Credit $credits, 
		\Magento\Customer\Model\Customer $customer,
		\Magento\Customer\Model\Session $customerSession,
		\Dckap\PurchaseReport\Model\Mail\TransportBuilder $transportBuilder,
		\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Psr\Log\LoggerInterface $logger,
		MessageInterface $message,
		array $data = []
		)
	{
		$this->_registry = $registry;
		$this->credits = $credits;
		$this->customerSession = $customerSession;
		$this->_resource = $resource;
		$this->_storeManager=$storeManager;
		$this->_currency = $currency;   
		$this->customer = $customer;
		$this->urlModel = $urlFactory->create();  		
		$this->_transportBuilder = $transportBuilder; 
		$this->inlineTranslation = $inlineTranslation;
		$this->scopeConfig = $scopeConfig;
		$this->message = $message;
		$this->logger = $logger;
		parent::__construct($context, $data);
	}
	
	public function getCreditFileUrl()
	{
		return $this->urlModel->getBaseUrl().'pub/media/dckap/customer_credit_line'; 

	}
	public function getCreditCollections($customer_id = NULL)
	{
		if ($this->customerSession->isLoggedIn()) {
			$customer_id = $this->customerSession->getId();
		}
		$collection = array();

		if($customer_id){
			$connection = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Framework\App\ResourceConnection');
			$conn = $connection->getConnection();
			$select = $conn->select()
			->from(
				['c' => 'customer_creditline']
				)
			->where('c.customer_id=?', $customer_id);
			$collection = $conn->fetchRow($select);
		}

		return $collection;
	}

	public function getCreditInfo() 
	{
		if( $this->customerSession->isLoggedIn()) {
			
			$customerid =  $this->customerSession->getCustomerId(); 
			$customer  = $this->customer->load($customerid); 
			
			if(! $this->customerSession->getData('p21_customerid')) {

				$this->customerSession->setData('p21_customerid', $customer->getData('p21_customerid'));
			}
			if($this->customerSession->getData('p21_customerid')) {

				$p21_customerid = $this->customerSession->getData('p21_customerid');
				$collection =$this->getCreditCollections($customerid);
				if($collection){	
					$credit_col = $this->credits->load($collection['id']);
					$credit_limit = $customer->getData('credit_limit');
						$credit_col->setCreditAmount($credit_limit);
						$credit_col->save();
						return true;
				}
			}
		}
		return false;
	}

	public function getCurrentCurrencySymbol()
	{
		return $this->_currency->getCurrencySymbol();
	}

	public function getEmailCopyTo($sender)
	{
		$data = $this->scopeConfig->getValue($sender, 
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		if (!empty($data)) {
			return $data;
		}
		return false;
	}

	public function sendEmailTemplate(
		$postObject,
		$sender,
		$addTo,
		$pdfFile
		) 
	{

		$this->inlineTranslation->suspend();
		$transport = $this->_transportBuilder

		->setTemplateIdentifier('credit_application_email_template') 
		->setTemplateOptions(
			[
			'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
			'store' => $this->_storeManager->getStore()->getId(),
			]
			)
		->setTemplateVars($postObject)
		->setFrom($sender)
		->addTo($addTo)
		->addAttachment(file_get_contents($pdfFile),'Credit Application')
		->getTransport();

		$transport->sendMessage();
		$this->inlineTranslation->resume();

		return $this;
	}
	public function p21customerid()
	{
		$p21_customerid = 0;
		if( $this->customerSession->isLoggedIn()) {
			$customerid =  $this->customerSession->getCustomerId(); 
			$p21_customerid = $this->customerSession->getData('p21_customerid');

			if(!$p21_customerid) {
				$customer  = $this->customer->load($customerid);
				$this->customerSession->setData('p21_customerid',$customer->getData('p21_customerid'));
				$p21_customerid = $this->customerSession->getData('p21_customerid');
			} 
			$this->logger->info("Customer_id".$p21_customerid);
			return $p21_customerid;               
		}			
	}
}
?>