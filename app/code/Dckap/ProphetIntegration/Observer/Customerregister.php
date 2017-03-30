<?php
namespace Dckap\ProphetIntegration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\Address;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\App\Area;

class Customerregister implements ObserverInterface
{
  const XML_PATH_REGISTER_EMAIL_IDENTITY = 'customer/create_account/email_identity';
  const XML_PATH_COPY_TO_CSR = 'sales_email/dckap_accounting/copy_to_csr';
  const XML_PATH_CSR_EMAIL_TEMPLATE= 'customer/create_account/email_confirmed_template_csr';

  protected $_registry = null;
  protected $logger;
  protected $customerFactory;
  protected $customerResourceFactory;
  protected $customer;
  protected $customerData;
  protected $objectManager;
  protected $address;
  protected $scopeConfig;
  protected $transportBuilder;
  protected $customerViewHelper;
  protected $customerRegistry;
  protected $dataProcessor;
  protected $inlineTranslation;

  public function __construct (
    \Magento\Framework\Registry $registry,
    \Psr\Log\LoggerInterface $logger,
    \Magento\Customer\Model\CustomerFactory $customerFactory,
    \Magento\Customer\Model\ResourceModel\CustomerFactory $customerResourceFactory,
    \Magento\Customer\Model\Customer $customer,
    \Magento\Customer\Model\Data\Customer $customerData,
    \Magento\Customer\Model\Address $address,
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Magento\Framework\ObjectManagerInterface $objectManager,
    CustomerViewHelper $customerViewHelper,
    \Dckap\PurchaseReport\Model\Mail\TransportBuilder $transportBuilder,
    \Magento\Customer\Model\CustomerRegistry $customerRegistry,
    \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
    DataObjectProcessor $dataProcessor
    ) {
    $this->_registry = $registry;
    $this->logger = $logger;
    $this->customerFactory = $customerFactory;
    $this->customerResourceFactory = $customerResourceFactory;
    $this->customer = $customer;
    $this->customerData = $customerData;
    $this->address = $address;
    $this->scopeConfig = $scopeConfig;
    $this->objectManager = $objectManager;
    $this->transportBuilder = $transportBuilder;
    $this->customerRegistry = $customerRegistry;
    $this->dataProcessor = $dataProcessor;
    $this->customerViewHelper = $customerViewHelper;
    $this->inlineTranslation = $inlineTranslation;
  }
  public function execute(\Magento\Framework\Event\Observer $observer) {

    $customer = $observer->getData('customer');

    $customerId = $observer->getData('customer')->getId();

    $pdfFile = $observer->getData('apply_credit_pdf');

    $contactid = rand();
    $p21_customerid = rand();

    $connection = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Framework\App\ResourceConnection');
    $conn = $connection->getConnection();
    
    $p21_customer_attribute_id = $conn->fetchAll("SELECT attribute_id FROM `eav_attribute` WHERE attribute_code='p21_customerid'");
    $data = $conn->query("insert into customer_entity_int value('',".$p21_customer_attribute_id[0]['attribute_id'].",".$customerId.",".$p21_customerid.")");
    
    $p21_contact_attribute_id = $conn->fetchAll("SELECT attribute_id FROM `eav_attribute` WHERE attribute_code='p21_contactid'");
    $data = $conn->query("insert into customer_entity_int value('',".$p21_contact_attribute_id[0]['attribute_id'].",".$customerId.",".$contactid.")");

    /* Sending the email report to CSR */
    /* Assigning the values to the variables */
    $customerEmailData = $this->getFullCustomerObject($customer);

    $address_detail = $this->address->load($customer->getDefaultBilling());

    $industry = 'N/A';
    foreach ($customerEmailData['custom_attributes'] as $key => $value) {
      if($value['attribute_code'] == 'industry') 
        $industry = $value['value'];
      if($value['attribute_code'] == 'is_p21_customer') 
        $p21_id = $value['value'];
    }
    if($address_detail['company'] == null){
      $address_detail['company'] = 'N/A';
    }
    if($p21_id){
      $currentcustomer = 'Existing Customer';
    }else{
      $currentcustomer = 'NEW Customer';
    }
    if(is_array($address_detail['street'])){
      $street = $address_detail['street'][0].', '.$address_detail['street'][1];
    }
    else{
      $street = $address_detail['street'];
    }

    $csr_email = $this->scopeConfig->getValue(self::XML_PATH_COPY_TO_CSR,
      \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

    $emailTemplateData = array();
    $emailTemplateData = ['customer' => $customerEmailData,'address' => $address_detail,'industry' => $industry, 'street' => $street, 'contactid' => $contactid, 'currentcustomer' => $currentcustomer];

    /* Sender Detail  */
    $senderInfo = [
    'name' => $this->getEmailCopyTo('trans_email/ident_support/name'),
    'email' => $this->getEmailCopyTo('trans_email/ident_support/email')
    ];
    /* Call to send email method */
    if ($csr_email) {

      $this->sendEmailTemplate($customer, self::XML_PATH_CSR_EMAIL_TEMPLATE, $senderInfo, $emailTemplateData, $customer->getStoreId(),  $csr_email,$pdfFile);
    }
    /* Email report sent to CSR */
  }
  /* your send mail method*/

  protected function sendEmailTemplate(
    $customer,
    $template,
    $sender,
    $templateParams = [],
    $storeId = null,
    $email = null,
    $pdfFile
    ) {
    $this->inlineTranslation->suspend();
    $templateId = $this->scopeConfig->getValue($template, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    if($pdfFile){
      $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
      ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
      ->setTemplateVars($templateParams)
      ->setFrom($sender)
      ->addTo($email, $this->customerViewHelper->getCustomerName($customer))
      ->addAttachment(file_get_contents($pdfFile),'Credit Application')
      ->getTransport();
    }else{
      $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
      ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
      ->setTemplateVars($templateParams)
      ->setFrom($sender)
      ->addTo($email, $this->customerViewHelper->getCustomerName($customer))
      ->getTransport();
    }
    $transport->sendMessage();
    $this->inlineTranslation->resume();

    return $this;
  }
  /**
  * Create an object with data merged from Customer and CustomerSecure
  *
  * @param CustomerInterface $customer
  * @return Data\CustomerSecure
  * @deprecated
  */
  protected function getFullCustomerObject($customer)
  {
  // No need to flatten the custom attributes or nested objects since the only usage is for email templates and
  // object passed for events
    $mergedCustomerData = $this->customerRegistry->retrieveSecureData($customer->getId());
    $customerData = $this->dataProcessor
    ->buildOutputDataArray($customer, '\Magento\Customer\Api\Data\CustomerInterface');
    $mergedCustomerData->addData($customerData);
    $mergedCustomerData->setData('name', $this->customerViewHelper->getCustomerName($customer));
    return $mergedCustomerData;
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
}
?>