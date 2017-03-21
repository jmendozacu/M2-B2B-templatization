<?php
namespace Dckap\Shoppinglist\Model;
class Shoppinglist extends \Magento\Framework\Model\AbstractModel implements ShoppinglistInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'Shoppinglist_names';
 
    protected function _construct()
    {
        $this->_init('Dckap\Shoppinglist\Model\ResourceModel\Shoppinglist');
    }
 
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}