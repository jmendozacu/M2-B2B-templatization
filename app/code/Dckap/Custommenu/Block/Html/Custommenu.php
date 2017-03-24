<?php
namespace Dckap\Custommenu\Block\Html;

/**
* Baz block
*/
class Custommenu
    extends \Magento\Framework\View\Element\Template
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
    
    public function getTitle()
    {
        return "Foo Bar Baz";
    }
}