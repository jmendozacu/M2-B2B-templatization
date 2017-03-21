<?php 
namespace Dckap\Shoppinglist\Controller\Index; 

use Magento\Customer\Model\Session;

class Deletelist extends \Magento\Framework\App\Action\Action {
    /** @var  \Magento\Framework\View\Result\Page */
    protected $resultPageFactory;
	protected $toDoFactory;
	protected $session;
    /**      * @param \Magento\Framework\App\Action\Context $context      */
    public function __construct(\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,\Dckap\Shoppinglist\Model\ProductlistFactory $toDoFactory, Session $customerSession)     {
         $this->resultPageFactory = $resultPageFactory;
		 $this->toDoFactory = $toDoFactory;
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
				
				$shoppinglistid = $this->getRequest()->getParam('shoppinglist_id');
				$todo = $this->toDoFactory->create();
				
				if($shoppinglistid) {
					$resources = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
					$connection= $resources->getConnection();
					$sql = "DELETE FROM shopping_list_item WHERE shopping_list_item_id = " . $shoppinglistid;
					$connection->query($sql);
					
					$this->session->setShoppinglistId('');
				}
			}
			

    }
}