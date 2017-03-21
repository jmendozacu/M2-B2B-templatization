<?php
/**
*
* Copyright © 2016 Magento. All rights reserved.
* See COPYING.txt for license details.
*/
namespace Dckap\OrderHistory\Controller\Order;

use Magento\Sales\Controller\OrderInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class History extends \Magento\Framework\App\Action\Action implements OrderInterface
{
    /**
    * @var PageFactory
    */
    protected $resultPageFactory;

    /**
    * @param Context $context
    * @param PageFactory $resultPageFactory
    */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
    * Customer order history
    *
    * @return \Magento\Framework\View\Result\Page
    */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $customerSession = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Customer\Model\Session');
        if($customerSession->getCustomer()->getId()){
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Order History'));

            $block = $resultPage->getLayout()->getBlock('customer.account.link.back');
            if ($block) {
                $block->setRefererUrl($this->_redirect->getRefererUrl());
            }
            return $resultPage;
        } else{
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login/');
            return $resultRedirect;
        }
    }
}