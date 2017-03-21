<?php
namespace Dckap\Topsellingitemscategory\Model;
 
use Magento\Framework\Model\AbstractModel;
 
class Topsellingitemscategory extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Dckap\Topsellingitemscategory\Model\Resource\Topsellingitemscategory');
    }
}