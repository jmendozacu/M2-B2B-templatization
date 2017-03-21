<?php
    namespace Dckap\PurchaseReport\Controller\Report;
   
    use Magento\Framework\App\Action\Context;
    use Magento\Framework\View\Result\PageFactory;
    

    class EmailReport extends \Magento\Framework\App\Action\Action
    {
    /**
* Recipient email config path
*/
const XML_PATH_EMAIL_RECIPIENT = 'contact/email/recipient_email';
/**
* @var \Magento\Framework\Mail\Template\TransportBuilder
*/
protected $_transportBuilder;
 protected $customerSession;
/**
* @var \Magento\Framework\Translate\Inline\StateInterface
*/
protected $inlineTranslation;

/**
* @var \Magento\Framework\App\Config\ScopeConfigInterface
*/
protected $scopeConfig;

/**
* @var \Magento\Store\Model\StoreManagerInterface
*/
protected $storeManager; 
/**
* @var \Magento\Framework\Escaper
*/
protected $_escaper;
/**
* @param \Magento\Framework\App\Action\Context $context
* @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
* @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
* @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
* @param \Magento\Store\Model\StoreManagerInterface $storeManager
*/
public function __construct(
\Magento\Framework\App\Action\Context $context,
//\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
\Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
\Dckap\PurchaseReport\Model\Mail\TransportBuilder $transportBuilder,
\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
\Magento\Framework\Escaper $escaper
) {
parent::__construct($context);
  $this->customerSession = $customerSession;
$this->_transportBuilder = $transportBuilder;
$this->inlineTranslation = $inlineTranslation;
$this->scopeConfig = $scopeConfig;
$this->storeManager = $storeManager;
$this->_escaper = $escaper;
}

/**
* Post user question
*
* @return void
* @throws \Exception
*/
public function execute()
{
    
    if ($this->customerSession->isLoggedIn()) {
             $email = $this->customerSession->getCustomer()->getData('email'); 
              $name = $this->customerSession->getCustomer()->getData('firstname');  
             }  
$post['name'] = 'Dckap';
$post['email'] = 'infoindia@dckap.com';
$post['customer_name'] = $name;
$this->inlineTranslation->suspend();
try {
$postObject = new \Magento\Framework\DataObject();
$postObject->setData($post);

$error = false;


$sender = [
'name' => $this->scopeConfig->getValue('trans_email/ident_support/name', 
          \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
'email' => $this->scopeConfig->getValue('trans_email/ident_support/email', 
          \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
];

/*$sender = [
'name' => $this->_escaper->escapeHtml('jeya'),
'email' => $this->_escaper->escapeHtml('jeyakiruthikag@dckap.com'),
];
*/

$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE; 
//$addTo = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, $storeScope);
$addTo = $email;
$transport = $this->_transportBuilder
->setTemplateIdentifier(39) // this code we have mentioned in the email_templates.xml
->setTemplateOptions(
[
'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
]
)
->setTemplateVars(['data' => $postObject])
->setFrom($sender)
->addTo($addTo)
->addAttachment($_POST['data'],'PurchaseReport')
->getTransport();

$transport->sendMessage(); ;
$this->inlineTranslation->resume();
echo 'Please check your email for your purchase report';
exit; 

return;
} catch (\Exception $e) {
    
print_r($e);
exit;
} 
}
    }       