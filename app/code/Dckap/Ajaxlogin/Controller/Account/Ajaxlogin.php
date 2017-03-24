<?php
namespace Dckap\Ajaxlogin\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

use Magento\Customer\Model\Account\Redirect as AccountRedirect;

use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\App\Config\ScopeConfigInterface;
class Ajaxlogin extends \Magento\Framework\App\Action\Action
{
     /**
     * @var PageFactory
     */
     protected $resultPageFactory;
     /** @var AccountManagementInterface */
     protected $customerAccountManagement;

     /** @var Validator */
     protected $formKeyValidator;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerUrl $customerHelperData
     * @param Validator $formKeyValidator
     * @param AccountRedirect $accountRedirect
     */
    /**
    
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerUrl $customerHelperData,
        Validator $formKeyValidator,
        AccountRedirect $accountRedirect,
        PageFactory $resultPageFactory
        
        ) {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerHelperData;
        $this->formKeyValidator = $formKeyValidator;
        $this->accountRedirect = $accountRedirect;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Customer order history
     *
     * @return \Magento\Framework\View\Result\Page
     */
        /**
     * Get scope config
     *
     * @return ScopeConfigInterface
     * @deprecated
     */
        private function getScopeConfig()
        {
            if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
                return \Magento\Framework\App\ObjectManager::getInstance()->get(
                    \Magento\Framework\App\Config\ScopeConfigInterface::class
                    );
            } else {
                return $this->scopeConfig;
            }
        }

    /**
     * Retrieve cookie manager
     *
     * @deprecated
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
                );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
                );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ($this->session->isLoggedIn()) {
         $message['url'] = 'success'; 
         $message['error'] = '';
     }

     if ($this->getRequest()->isPost()) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $login = $this->getRequest()->getPost();
        if (!empty($login['email']) && !empty($login['pass'])) {
            if(!empty($login['rememberme']) && $login['rememberme'] === 'true'){
                $logindetails = array('username'=>$login['email'],'password'=>$login['pass'],'remchkbox'=>1);
                $logindetails = json_encode($logindetails);
                $objectManager->get('Dckap\Ajaxlogin\Remembermecookie')->set($logindetails,604800);
            }else{
                $objectManager->get('Dckap\Ajaxlogin\Remembermecookie')->delete('remeber');
            }
            try {
                $customer = $this->customerAccountManagement->authenticate($login['email'], $login['pass']);
                $this->session->setCustomerDataAsLoggedIn($customer);
                $this->session->regenerateId();
                $message['url'] ='success';
                $message['error'] ='';
                if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                    $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                    $metadata->setPath('/');
                    $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                }
                
					$redirectUrl= $_SERVER['HTTP_REFERER'];//$this->accountRedirect->getRedirectCookie();
					//exit;
                 
                    if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                        $this->accountRedirect->clearRedirectCookie();
                        $resultRedirect = $this->resultRedirectFactory->create();
                        // URL is checked to be internal in $this->_redirect->success()
                        $message['url'] = $redirectUrl;
                        $message['error'] = '';
                    }
                } catch (EmailNotConfirmedException $e) {
                    $value = $this->customerUrl->getEmailConfirmationUrl($login['email']);
                    $message['error'] = 'This account is not confirmed. <a href="'.$value.'">Click here</a> to resend confirmation email.';
                    $message['url'] = '';
                    $this->session->setUsername($login['email']);
                } catch (UserLockedException $e) {
                    $message['error'] =  'The account is locked. Please wait and try again or contact "'.$this->getScopeConfig()->getValue('contact/email/recipient_email').'".';
                    //$this->messageManager->addError($message);
                    $message['url'] = '';
                    $this->session->setUsername($login['email']);
                } catch (AuthenticationException $e) {
                    $message['error'] =  'Invalid login or password.';
                    //$this->messageManager->addError($message);
                    $message['url'] = '';
                    $this->session->setUsername($login['email']);
                } catch (\Exception $e) {
                    // PA DSS violation: throwing or logging an exception here can disclose customer password
                    $message['error'] = 'An unspecified error occurred. Please contact us for assistance.';
                    $message['url'] = '';
                }
            } else {
              $message['error'] = 'A login and a password are required.';
              $message['url'] = '';
          }
      }
      
      echo json_encode($message);
      exit;
  }
}       