<?php
namespace Dckap\Quoteproducts\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
    * @var \Magento\Framework\View\Result\PageFactory
    */
    protected $_resultPageFactory;

    /**
    * [__construct]
    * @param Context $context
    * @param PageFactory $resultPageFactory
    */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
        ) {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct(
            $context
            );
    }
 
    /**
    * loads custom layout
    *
    * @return \Magento\Framework\View\Result\Page
    */
    public function execute()
    { 
        $customerSession = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Customer\Model\Session');
        if($customerSession->getCustomer()->getId()){
            $resultPage = $this->_resultPageFactory->create();
            $this->_view->getPage()->getConfig()->getTitle()->set(__('My Quoted Items'));
            return $resultPage;
        }else{
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login/');
            return $resultRedirect;
        }
    }
}