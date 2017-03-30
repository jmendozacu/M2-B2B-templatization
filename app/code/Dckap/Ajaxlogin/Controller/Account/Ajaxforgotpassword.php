<?php
namespace Dckap\Ajaxlogin\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;

use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;

class Ajaxforgotpassword extends \Magento\Framework\App\Action\Action
{
    /**
    * @var PageFactory
    */
    protected $resultPageFactory;
    /** @var AccountManagementInterface */
    protected $customerAccountManagement;

    /** @var Escaper */
    protected $escaper;

    /**
    * @var Session
    */
    protected $session;

    /**

    * @param PageFactory $resultPageFactory
    */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        Escaper $escaper,
        PageFactory $resultPageFactory

        ) {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->escaper = $escaper;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {

        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $email = (string)$this->getRequest()->getPost('email');
        if ($email) {


            try {
                $this->customerAccountManagement->initiatePasswordReset(
                    $email,
                    AccountManagement::EMAIL_RESET
                    );

            } catch (NoSuchEntityException $exception) {
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch (SecurityViolationException $exception) {
                $message['error'] = $exception->getMessage();

            } catch (\Exception $exception) {
                $message['error'] = 'We\'re unable to send the password reset email.';

            }
            $message['error'] =  $this->getSuccessMessage($email);

        } else {
            $message['error'] = 'Please enter your email.';

        }
        echo json_encode($message);
        exit();
    }

    /**
    * Retrieve success message
    *
    * @param string $email
    * @return \Magento\Framework\Phrase
    */
    protected function getSuccessMessage($email)
    {
        return __(
            'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            $this->escaper->escapeHtml($email)
            );
    }
}       