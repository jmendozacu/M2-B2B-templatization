<?php 
namespace Dckap\Shoppinglist\Controller\Index; 

use Magento\Customer\Model\Session;

class Updateqty extends \Magento\Framework\App\Action\Action {
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

            $todo = $this->toDoFactory->create();

            $product_id = $this->getRequest()->getParam('pid');
            $shoppinglistid = $this->getRequest()->getParam('slistid');
            $qty = $this->getRequest()->getParam('qty');
            $connection = \Magento\Framework\App\ObjectManager::getInstance();
            $conn = $connection->create('\Magento\Framework\App\ResourceConnection')->getConnection();
            $data = $conn->query("UPDATE shopping_list_item SET qty = ".$qty." WHERE shopping_list_id = ".$shoppinglistid." and item_id = ".$product_id);
		}		
    }
}