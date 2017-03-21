<?php
    namespace Dckap\PurchaseReport\Controller\Report;
   
    use Magento\Framework\App\Action\Context;
    use Magento\Framework\View\Result\PageFactory;
    

    class Purchasebyproductcategory extends \Magento\Framework\App\Action\Action
    {
     /**
     * @var PageFactory
     */
    protected $resultPageFactory;

   

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */

   
    protected $purchasereport;

    public function __construct(
        Context $context,
        \Dckap\PurchaseReport\Block\Purchasereport $purchasereport,
        PageFactory $resultPageFactory
        
    ) {

         $this->purchasereport = $purchasereport;
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
                      
          $params = $this->getRequest()->getParams();
             $rolling_year = ($params['roll_year']); 
         $output = $this->purchasereport->getOrdersByProductCategory($rolling_year);
          print_r($output);
         exit();
        }
    }       