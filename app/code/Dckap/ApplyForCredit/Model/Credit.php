<?php
namespace Dckap\ApplyForCredit\Model;

class Credit extends \Magento\Framework\Model\AbstractModel
{
protected function _construct()
    {
        $this->_init('Dckap\ApplyForCredit\Model\ResourceModel\Credit');
    }
    
}