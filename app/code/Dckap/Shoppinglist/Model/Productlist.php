<?php
namespace Dckap\Shoppinglist\Model;
class Productlist extends \Magento\Framework\Model\AbstractModel implements ProductlistInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'Shoppinglist_Product_names';
 
    protected function _construct()
    {
        $this->_init('Dckap\Shoppinglist\Model\ResourceModel\Productlist');
    }
 
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getShoppingListItemId()];
    }
}