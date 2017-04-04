<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dckap\Requestquote\Model\ResourceModel\Requestforquote;

use \Magento\Cms\Model\ResourceModel\AbstractCollection;

/**
 * CMS page collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'requestquote_id';
   
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Dckap\Requestquote\Model\Requestforquote', 'Dckap\Requestquote\Model\ResourceModel\Requestquote');
        $this->_map['fields']['requestquote_id'] = 'main_table.requestquote_id';        
    }

    protected function _initSelect()
    {
        //get salesrepresentative details
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $admin_session = $objectManager->get('\Magento\Backend\Model\Auth\Session');
        $user_id = $admin_session->getUser()->getData('user_id');
        parent::_initSelect();
    }
    
    public function addStoreFilter($store, $withAdmin = true)
    {
        return $this;
    }    
}