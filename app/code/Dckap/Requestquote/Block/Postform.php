<?php
namespace Dckap\Requestquote\Block;

use Magento\Framework\View\Element\Template;
 
class Postform extends Template
{ 
        
        protected $scopeConfig;
        
        
        public function __construct( \Magento\Framework\View\Element\Template\Context $context,
                                    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig) {
        
                $this->scopeConfig = $scopeConfig;
                parent::__construct($context);
        }
        
        
               
        public function getFormAction()
        {
            return $this->getUrl('requestquote/index/post', ['_secure' => true]);
        }
        
}