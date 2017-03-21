<?php
namespace Dckap\Shoppinglist\Model\ResourceModel\Productlist;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Dckap\Shoppinglist\Model\Productlist','Dckap\Shoppinglist\Model\ResourceModel\Productlist');
    }
}