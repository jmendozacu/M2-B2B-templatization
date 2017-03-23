<?php
 
namespace Dckap\Changeprice\Plugin;
 
class Changeprice
{
	private $session;
	public function __construct (\Magento\Customer\Model\Session $session){
		$this->session = $session;
	}
	
    public function afterGetPrice(\Magento\Catalog\Model\Product $subject, $result){
    	if($this->session->isLoggedIn()) {
    		$custom_price = '';
			$id = $this->session->getCustomerId();
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
			$customer = $objectManager->create('Magento\Customer\Model\Customer')->load($id);
			$jsonoutput = $customer->getData('custom_product_price');
			$custom_price = json_decode($jsonoutput,true);
				if(array_key_exists($subject->getSku(),$custom_price))
				return $custom_price[$subject->getSku()];
				else
				return $result;
		}
		return $result;
    }
}