<?php
namespace Dckap\Shoppinglist\Block;

use Magento\Customer\Model\Session;
class Shoppinglist extends \Magento\Framework\View\Element\Template
{
    protected $shoppinglistFactory;
	protected $productlistFactory;
	protected $session;
	protected $connection;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Dckap\Shoppinglist\Model\ShoppinglistFactory $shoppinglistFactory,
		\Dckap\Shoppinglist\Model\ProductlistFactory $productlistFactory,
		\Magento\Framework\App\ResourceConnection $connection,
		Session $customerSession
    )
    {
        $this->shoppinglistFactory = $shoppinglistFactory;
		$this->productlistFactory = $productlistFactory;
		$this->session = $customerSession;
		$this->connection = $connection;
        parent::__construct($context);
    }
 
    function _prepareLayout()
    {
    	
		   
	}
	
	public function getShoppinglist() {
		$connection = $this->connection;//\Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Framework\App\ResourceConnection');
          $conn = $connection->getConnection();
          $select = $conn->select()
              ->from(
                  ['s' => 'shopping_list']
              )
              ->where('s.customer_id=?', $this->session->getId());
          $collection = $conn->fetchAll($select);
		  
		return $collection;
	}
	
	public function getShoppinglistProduct($item_id) {
		  $connection = $this->connection;//\Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Framework\App\ResourceConnection');
          $conn = $connection->getConnection();
          $select = $conn->select()
              ->from(
                  ['s' => 'shopping_list_item']
              )
              ->where('s.shopping_list_id=?', $item_id);
          $data = $conn->fetchAll($select);
		return $data;
	}
	
	public function getShoppinglistId() {
	
	$session = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Session');
	
	return $session->getShoppinglistId();
	}
	
	public function getProductOptionLabel($option_id) {
		$connection = $this->connection;
		$conn = $connection->getConnection();
          $select = $conn->select()
              ->from(
                  ['e' => 'eav_attribute_option_value']
              )
              ->where('e.option_id=?', $option_id);
          $data = $conn->fetchAll($select);
		foreach($data as $datum)
		return $datum['value'];
	}
}