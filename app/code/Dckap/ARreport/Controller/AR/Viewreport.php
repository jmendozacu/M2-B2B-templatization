<?php
    namespace Dckap\ARreport\Controller\AR;
   
    use Magento\Framework\App\Action\Context;
    use Magento\Framework\View\Result\PageFactory;
    
    class Viewreport extends \Magento\Framework\App\Action\Action
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
            $customerSession = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Customer\Model\Session');
            if($customerSession->getCustomer()->getId()){

                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->set(__('AR-Reports'));
                return $resultPage;
            }else{
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('customer/account/login/');
                return $resultRedirect;
            }
        }
    }       