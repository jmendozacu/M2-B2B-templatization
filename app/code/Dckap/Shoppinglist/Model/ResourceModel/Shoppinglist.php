<?php
namespace Dckap\Shoppinglist\Model\ResourceModel;
class Shoppinglist extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('shopping_list','id');
    }
}