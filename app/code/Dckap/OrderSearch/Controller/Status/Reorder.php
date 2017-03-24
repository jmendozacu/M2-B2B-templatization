<?php
namespace Dckap\OrderSearch\Controller\Status;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\RedirectFactory;


class Reorder extends \Magento\Framework\App\Action\Action
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
        $orderId = $this->getRequest()->getParam('order_id'); 
        $order = $this->orderFactory->create()->load($orderId);
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        /* @var $cart \Magento\Checkout\Model\Cart */
        $cart = $this->_objectManager->get('Magento\Checkout\Model\Cart');
        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                if ($this->_objectManager->get('Magento\Checkout\Model\Session')->getUseNotice(true)) {
                    $this->messageManager->addNotice($e->getMessage());
                } else {
                    $this->messageManager->addError($e->getMessage());
                }
                return $resultRedirect->setPath('*/*/history');
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
                return $resultRedirect->setPath('checkout/cart');
            }
        }

        $cart->save();
        return $resultRedirect->setPath('checkout/cart');
    }
}       