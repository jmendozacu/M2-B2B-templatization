<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dckap\Theme\Block;

/**
 * Links list block
 */
class Links extends \Magento\Framework\View\Element\Template
{
    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,
    \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context);
        
    }
    
     public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    public function isCustomerLoggedIn()
    {
    return $this->customerSession;
    } 
}
