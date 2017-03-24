<?php
namespace Dckap\Topsellingitems\Model;
 
use Magento\Framework\Model\AbstractModel;
 
class Topsellingitems extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Dckap\Topsellingitems\Model\Resource\Topsellingitems');
    }
}