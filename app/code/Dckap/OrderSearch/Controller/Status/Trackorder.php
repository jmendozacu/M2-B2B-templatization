<?php
namespace Dckap\OrderSearch\Controller\Status;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\RedirectFactory;


class Trackorder extends \Magento\Framework\App\Action\Action
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
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Order Status'));
        return $resultPage;
    }
}       