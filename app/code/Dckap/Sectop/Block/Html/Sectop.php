<?php
namespace Dckap\Sectop\Block\Html;

/**
* Baz block
*/
use Magento\Customer\Model\Session;
class Sectop  extends \Magento\Framework\View\Element\Template
{

protected $session;

	public function __construct(\Magento\Framework\View\Element\Template\Context $context,Session $customerSession)
	{
    
    $this->session = $customerSession;
    parent::__construct($context);
   
	}
	public function getCustomerloggedin()
	{
		if ($this->session->getId()) {
   	return 1;
			} else {
			   return 0;
			}
	}
    public function getTitle()
    {
        return "Foo Bar Baz";
    }
}