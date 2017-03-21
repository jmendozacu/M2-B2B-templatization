<?php 
namespace Dckap\Shoppinglist\Controller\Index; 

use Magento\Customer\Model\Session;

class Index extends \Magento\Framework\App\Action\Action {
    /** @var  \Magento\Framework\View\Result\Page */
    protected $resultPageFactory;
	
	protected $session;
    /**      * @param \Magento\Framework\App\Action\Context $context      */
    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory, Session $customerSession)     {
        $this->resultPageFactory = $resultPageFactory;
		$this->session = $customerSession;
        parent::__construct($context);
    }

    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
		if ($this->session->isLoggedIn()) {
			
			$resultPage = $this->resultPageFactory->create();
			$resultPage->getConfig()->getTitle()->set(__('Shopping List'));
			//$resultPage->getConfig()->getTitle()->prepend(__('Dckap HelloWorld'));
			return $resultPage;
		}
		$resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customer/account/login/');
        return $resultRedirect;
    }
}