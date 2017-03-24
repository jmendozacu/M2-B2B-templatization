<?php 
namespace Dckap\Shoppinglist\Controller\Index; 

use Magento\Customer\Model\Session;

class Updatelistmob extends \Magento\Framework\App\Action\Action {
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
            $this->session->setShoppinglistId($shoppinglistid);
            $connection = \Magento\Framework\App\ObjectManager::getInstance();
            $conn = $connection->create('\Magento\Framework\App\ResourceConnection')->getConnection();
            $data = $conn->fetchRow("select * from shopping_list_item where shopping_list_id=".$shoppinglistid." and item_id=".$product_id);
            if(empty($data)) {
                $todo = $this->toDoFactory->create();
                $todo->setData('shopping_list_id',$shoppinglistid)
                    ->setData('item_id',$product_id)
                    ->setData('qty',1)
                    ->save();
                    $lastid = $todo->getShoppingListItemId();
                $product_obj = $connection->get('Magento\Catalog\Model\Product')->load($product_id);
                $len = $connection->get('Dckap\Shoppinglist\Block\Shoppinglist')->getProductOptionLabel($product_obj->getLength());
                echo "<tr><td>".$product_obj->getSku()."</td><td><input name='qty' style='width:40px;' value='1' type='text' \><input type='hidden' name='pid' id='pid' value='".$product_id."' /></td><td><input type='checkbox' name='bulk[]' style='width:30px;' checked='checked' value='".$product_id."' /></td><td><a title='Delete Row' href='javascript:void(0);' class='delete-ico dckap-sprite'></a><input type='hidden' value='".$lastid."' ></td></tr>";
                exit;
            }
		      
		}		
    }
}