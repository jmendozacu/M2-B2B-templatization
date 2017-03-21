<?php
namespace Dckap\Shoppinglist\Model\ResourceModel\Shoppinglist;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Dckap\Shoppinglist\Model\Shoppinglist','Dckap\Shoppinglist\Model\ResourceModel\Shoppinglist');
    }
}