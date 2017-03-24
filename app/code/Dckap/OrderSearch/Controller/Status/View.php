<?php
namespace Dckap\OrderSearch\Controller\Status;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\RedirectFactory;


class View extends \Magento\Framework\App\Action\Action
{
	/**
	* @var PageFactory
	*/
	protected $resultPageFactory;

	protected $orderFactory;

	/**
	* @var \Magento\Framework\Registry
	*/
	protected $registry;

	/**
	* @param Context $context
	* @param PageFactory $resultPageFactory
	*/
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory,
		OrderFactory $orderFactory,
		Registry $registry        
		) {
		$this->resultPageFactory = $resultPageFactory;
		$this->orderFactory = $orderFactory;
		$this->registry = $registry;

		parent::__construct($context);
	}

	/**
	* Customer order history
	*
	* @return \Magento\Framework\View\Result\Page
	*/
	public function execute()
	{
		$orderId = $this->getRequest()->getParam('sch_order_num');
		$email = $this->getRequest()->getParam('sch_email_address');
		$zipcode = $this->getRequest()->getParam('sch_zipcode');

		$order = $this->orderFactory->create()->loadByIncrementId($orderId);

		if($order->getIncrementId()) {
			if($order->getIncrementId()==$orderId && $order->getBillingAddress()->getData('postcode')==$zipcode && $order->getBillingAddress()->getData('email') ==strtolower($email)) {
				$this->registry->register('current_order', $order);
				$resultPage = $this->resultPageFactory->create();
				$resultPage->getConfig()->getTitle()->set(__('Order # '.$order->getIncrementId()));
				return $resultPage;
			}
			else {
				if ($this->getRequest()->getParam('desktop')=='desktop') {
					$this->messageManager->addError( __('Supplied Order Information not Matched! If you don\'t have this information please <a href="mailto:infoindia@dckap.com" target="_blank"><u>Email Us.</u></a> ') );
				}else if($this->getRequest()->getParam('desktop')=='mobile')
				$this->messageManager->addError( __('Supplied Order Information not Matched! If you don\'t have this information please <a href="tel:877-872-3252" ><u>Call Us.</u></a> ') );
				$resultRedirect = $this->resultRedirectFactory->create();
				$resultRedirect->setPath('/');
				return $resultRedirect;


			}
		}

		if ($this->getRequest()->getParam('desktop')=='desktop') {
			$this->messageManager->addError( __('Supplied Order Information not Matched! If you don\'t have this information please <a href="mailto:infoindia@dckap.com" target="_blank"><u>Email Us.</u></a>') );
		}else if($this->getRequest()->getParam('desktop')=='mobile'){
			$this->messageManager->addError( __('Supplied Order Information not Matched! If you don\'t have this information please <a href="tel:877-872-3252" ><u>Call Us.</u></a> ') );
		}
		$resultRedirect = $this->resultRedirectFactory->create();
		$resultRedirect->setPath('/');
		return $resultRedirect;
	}
}       