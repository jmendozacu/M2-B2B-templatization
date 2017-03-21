<?php
namespace Dckap\Shoppinglist\Model\ResourceModel;
class Productlist extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('shopping_list_item','shopping_list_item_id');
    }
}