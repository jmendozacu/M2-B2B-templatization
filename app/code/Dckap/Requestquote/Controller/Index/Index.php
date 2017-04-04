<?php
namespace Dckap\Requestquote\Controller\Index;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;


class Index extends \Dckap\Requestquote\Controller\Index
{
                
        public function execute()
        {
        	//$customerSession = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Customer\Model\Session');
        	//if($customerSession->getCustomer()->getId()){
            $this->_view->loadLayout();
            $this->_view->getPage()->getConfig()->getTitle()->set(__('My Request for Quote'));
            $this->_view->renderLayout();
        	//}else{
        	//$resultRedirect = $this->resultRedirectFactory->create();
        	//$resultRedirect->setPath('customer/account/login/');
        	//return $resultRedirect;
        	//}
        }
        
}