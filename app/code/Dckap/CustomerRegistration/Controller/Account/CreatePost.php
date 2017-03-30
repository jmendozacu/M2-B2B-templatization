<?php
/**
* Copyright © 2015 Magento. All rights reserved.
* See COPYING.txt for license details.
*/
namespace Dckap\CustomerRegistration\Controller\Account;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Escaper;
use Magento\Newsletter\Model\SubscriberFactory;

/**
* @SuppressWarnings(PHPMD.CouplingBetweenObjects)
*/
class CreatePost extends \Magento\Customer\Controller\Account\CreatePost
{
  /** @var AccountManagementInterface */
  protected $accountManagement;

  /** @var Address */
  protected $addressHelper;

  /** @var FormFactory */
  protected $formFactory;

  /** @var SubscriberFactory */
  protected $subscriberFactory;

  /** @var RegionInterfaceFactory */
  protected $regionDataFactory;

  /** @var AddressInterfaceFactory */
  protected $addressDataFactory;

  /** @var Registration */
  protected $registration;

  /** @var CustomerInterfaceFactory */
  protected $customerDataFactory;

  /** @var CustomerUrl */
  protected $customerUrl;

  /** @var Escaper */
  protected $escaper;

  /** @var CustomerExtractor */
  protected $customerExtractor;

  /** @var \Magento\Framework\UrlInterface */
  protected $urlModel;

  /** @var DataObjectHelper  */
  protected $dataObjectHelper;

  /**
  * @var Session
  */
  protected $session;

  /**
  * @var AccountRedirect
  */
  protected $_objectManager = null;
  private $accountRedirect;
  private $addressRepository;
  private $adapterFactory;
  private $uploader;
  private $filesystem;
  protected $messageManager;
  protected $email;
  protected $inlineTranslation;

  public function __construct(\Magento\MediaStorage\Model\File\UploaderFactory $uploader,
    \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
    \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
    \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory,
    \Magento\Customer\Model\Metadata\FormFactory $formFactory,
    \Magento\Framework\Event\ManagerInterface $eventManager,
    \Magento\Framework\Message\ManagerInterface $messageManager, 
    \Magento\Framework\App\ActionFlag $actionFlag,
    \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Customer\Model\Registration $registration,
    \Magento\Customer\Model\CustomerExtractor $customerExtractor,
    \Magento\Framework\Filesystem $filesystem,
    \Magento\Customer\Api\AccountManagementInterface $accountManagement,
    \Magento\Framework\UrlFactory $urlFactory, 
    \Magento\Framework\App\Response\RedirectInterface $redirect,
    \Magento\Customer\Model\Account\Redirect $accountRedirect,
    \Magento\Customer\Helper\Address $addressHelper,
    \Magento\Customer\Model\Url $customerUrl,
    \Magento\Customer\Model\CustomerFactory $customerFactory,
    \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
    \Magento\Framework\Escaper $escaper,
    \Magento\Framework\ObjectManagerInterface $objectManager,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
    SubscriberFactory $subscriberFactory,
    \Magento\Framework\Mail\Template\TransportBuilder $email
    )
  {
    $this->uploader = $uploader; 
    $this->dataObjectHelper = $dataObjectHelper;
    $this->_objectManager = $objectManager;
    $this->addressDataFactory = $addressDataFactory;
    $this->regionDataFactory = $regionDataFactory;
    $this->formFactory = $formFactory; 
    $this->_eventManager = $eventManager; 
    $this->messageManager = $messageManager;
    $this->_actionFlag = $actionFlag;
    $this->resultRedirectFactory = $resultRedirectFactory;
    $this->session = $customerSession;
    $this->registration = $registration;
    $this->customerExtractor = $customerExtractor;
    $this->filesystem = $filesystem;
    $this->accountManagement = $accountManagement;
    $this->urlModel = $urlFactory->create();
    $this->_redirect = $redirect;
    $this->accountRedirect = $accountRedirect;
    $this->addressHelper = $addressHelper;
    $this->customerUrl = $customerUrl;
    $this->_customerFactory = $customerFactory;
    $this->customerRepository = $customerRepository;
    $this->escaper = $escaper;
    $this->storeManager = $storeManager;
    $this->email = $email;
    $this->subscriberFactory = $subscriberFactory;
    $this->inlineTranslation = $inlineTranslation;
  }
  public function execute()
  {
    $value =''; 
    $p21_chk ='';
    if(!empty($_FILES)){
      $value = $_FILES['fileToUpload']['name'];
    }
    $this->getRequest()->setParam('credit_line',$value);
    
    $title = $this->getRequest()->getParam('title');
    $this->getRequest()->setParam('title',$title);
    
    $industry = $this->getRequest()->getParam('industry');
    $this->getRequest()->setParam('industry',$industry);

    $company =  $this->getRequest()->getParam('company');
    $this->getRequest()->setParam('reg_companyname',$company);

    $resultRedirect = $this->resultRedirectFactory->create();
    if ($this->session->isLoggedIn() || !$this->registration->isAllowed()) 
    {
      $resultRedirect->setPath('*/*/');
      return $resultRedirect;
    }

    if (!$this->getRequest()->isPost()) 
    {

      $url = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
      $resultRedirect->setUrl($this->_redirect->error($url));
      return $resultRedirect;
    }

    $this->session->regenerateId();

    $p21_chk = $this->getRequest()->getParam('is_p21_customer');
    $email =  $this->getRequest()->getParam('email');
    $fname =  $this->getRequest()->getParam('firstname');
    $lname =  $this->getRequest()->getParam('lastname');
    $company =  $this->getRequest()->getParam('company');
    $telephone =  $this->getRequest()->getParam('telephone');
    $streetaddress = $this->getRequest()->getParam('street');
    $city = $this->getRequest()->getParam('city');
    $state = $this->getRequest()->getParam('region_id');
    $country = $this->getRequest()->getParam('country_id');
    $zipcode = $this->getRequest()->getParam('postcode');

    $address = $this->extractAddress();
    $addresses = $address === null ? [] : [$address];

    $customer = $this->customerExtractor->extract('customer_account_create', $this->_request);
    $customer->setAddresses($addresses);

    $password = $this->getRequest()->getParam('password');
    $confirmation = $this->getRequest()->getParam('password_confirmation');

    $redirectUrl = $this->session->getBeforeAuthUrl();
    $this->checkPasswordConfirmation($password, $confirmation);
    try {

      $customer = $this->accountManagement->createAccount($customer, $password, $redirectUrl);

      if ($this->getRequest()->getParam('is_subscribed', false)) {
        $email = $customer->getEmail();                
        $status = $this->subscriberFactory->create()->subscribe($email);
        $this->subscriberFactory->create()->subscribeCustomerById($customer->getId());
      }

      $pdfFile = '';
      if (isset($_FILES['fileToUpload']) && isset($_FILES['fileToUpload']['name']) && strlen($_FILES['fileToUpload']['name'])) {

        $customer_id = $customer->getId();
        $base_media_path = 'dckap/customer_credit_line';
        $uploader = $this->uploader->create(
          ['fileId' => 'fileToUpload']
          );
        $uploader->setAllowedExtensions(['pdf']);                    
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(true);

        $mediaDirectory = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

        $result = $uploader->save($mediaDirectory->getAbsolutePath($base_media_path));

        $credits = $this->_objectManager->create('Dckap\ApplyForCredit\Model\Credit');


        $blocks = $this->_objectManager->create('Dckap\ApplyForCredit\Block\Credit');
        $collection = $blocks->getCreditCollection();

        if(!empty($collection)) {   
          $credit_files_db = array();
          $get_credit_data = $this->_objectManager->get('Dckap\ApplyForCredit\Model\Credit')->load($collection['id']);
          $credit_files_db = json_decode($collection['credit_file'],true);  

          $count = count($credit_files_db);                      
          $credit_files_db1 [$count] = $result['file'];; 
          $obj_merged =  array_merge( $credit_files_db, $credit_files_db1);
          $filename = json_encode($obj_merged);
          $get_credit_data->setCreditFile("$filename");
          $get_credit_data->save();
        }else {
          $credit_files_upload[0] = $result['file'];
          $filename = json_encode($credit_files_upload);
          $credits->setCustomerId($customer_id);
          $credits->setCreditFile("$filename");
          $credits->save();
        }

        $pdfFile = 'pub/media/dckap/customer_credit_line'.$result['file'];

        /* Email sent to Accounting*/
      }
      $this->_eventManager->dispatch(
        'customer_register_success',
        ['account_controller' => $this, 'customer' => $customer, 'apply_credit_pdf' => $pdfFile]
        );

      $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());

      if ($confirmationStatus ===  \Magento\Customer\Api\AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) 
      {
        $email = $this->customerUrl->getEmailConfirmationUrl($customer->getEmail());

        $this->messageManager->addSuccess(
          __(
            'You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.',
            $email
            )
          );

        $url = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
        $resultRedirect->setUrl($this->_redirect->success($url));

      } else {
        $this->session->setCustomerDataAsLoggedIn($customer);
        $this->messageManager->addSuccess($this->getSuccessMessage());
        $resultRedirect = $this->accountRedirect->getRedirect();
      } 
      return $resultRedirect;
    }     
    catch (StateException $e) 
    {
      $url = $this->urlModel->getUrl('customer/account/forgotpassword');
      $message = __(
        'There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.',
        $url
        );
      $this->messageManager->addError($message);
    } catch (InputException $e) 
    {
      $this->messageManager->addError($this->escaper->escapeHtml($e->getMessage()));
      foreach ($e->getErrors() as $error) {
        $this->messageManager->addError($this->escaper->escapeHtml($error->getMessage()));
      }
    } 
    catch (\Exception $e) 
    {
      $this->messageManager->addException($e,$e->getMessage());  
    }
    $this->session->setCustomerFormData($this->getRequest()->getPostValue());
    $defaultUrl = $this->urlModel->getUrl('*/*/create', ['_secure' => true]);
    $resultRedirect->setUrl($this->_redirect->error($defaultUrl));
    return $resultRedirect;
  }
}
