<?php
namespace Dckap\Quickorder\Block;

class Quickorder extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{    
    public $_scopeConfig;
    public $_storeManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider       
    ) {
        parent::__construct($context, $data = []);
        $this->_scopeConfig        = $context->getScopeConfig();
        $this->_storeManager       = $context->getstoreManager();   
    }       

    public function getIdentities() {
        return ['quickorder_list'];
    }   
}