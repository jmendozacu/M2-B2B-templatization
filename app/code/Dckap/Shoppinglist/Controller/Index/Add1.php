<?php 
namespace Dckap\Shoppinglist\Controller\Index; 

use Magento\Customer\Model\Session;

class Add extends \Magento\Framework\App\Action\Action {
    /** @var  \Magento\Framework\View\Result\Page */
    protected $resultPageFactory;
	protected $toDoFactory;
	protected $session;
	protected $messageManager;
    /**      * @param \Magento\Framework\App\Action\Context $context      */
    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,\Dckap\Shoppinglist\Model\ShoppinglistFactory $toDoFactory, Session $customerSession,\Magento\Framework\Message\ManagerInterface $messageManager)     {
         $this->resultPageFactory = $resultPageFactory;
		 $this->toDoFactory = $toDoFactory;
		 $this->session = $customerSession;
		 $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * Blog Index, shows a list of recent blog posts.
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
		$resultRedirect = $this->resultRedirectFactory->create();
		if ($this->session->isLoggedIn()) {
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$customer = $this->session->getId();
			$shoppingname = $this->getRequest()->getParam('shoppinglist_name');
			$todo = $this->toDoFactory->create();
			$todo->setData('list_name',$shoppingname)
			->setData('customer_id',$customer)
			->save();
			$this->messageManager->addSuccess( __(' Shoppinglist Created Successfully.') );
			$resultRedirect->setPath($this->_redirect->getRefererUrl());
			return $resultRedirect;
		}
			
			$resultRedirect->setPath('customer/account/login/');
			return $resultRedirect;
    }
}