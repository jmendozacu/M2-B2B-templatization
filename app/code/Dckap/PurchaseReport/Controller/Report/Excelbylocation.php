<?php
    namespace Dckap\PurchaseReport\Controller\Report;
   
    use Magento\Framework\App\Action\Context;
    use Magento\Framework\View\Result\PageFactory;
    

    class Excelbylocation extends \Magento\Framework\App\Action\Action
    {
     /**
     * @var PageFactory
     */
    protected $resultPageFactory;

   protected $fileFactory;
    protected $purchasereport;

    public function __construct(
        Context $context,
        \Dckap\PurchaseReport\Block\Purchasereport $purchasereport,
        
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        PageFactory $resultPageFactory
        
    ) {
       
        $this->fileFactory           = $fileFactory;
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
          $output = json_decode($this->purchasereport->getOrdersByLocation());
        
          $fileName = 'd.xls'; 
            $convert = new \Magento\Framework\Convert\Excel(new \ArrayIterator($output));
            $content = $convert->convert('single_sheet');
            
            return $this->fileFactory->create(
            $fileName,
            $content, //content here. it can be null and set later 
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA,
             'application/octet-stream',
             true );
          
         exit();
        }
    }       