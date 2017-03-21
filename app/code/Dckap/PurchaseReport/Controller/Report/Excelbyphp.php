<?php
    namespace Dckap\PurchaseReport\Controller\Report;
   
    use Magento\Framework\App\Action\Context;
    use Magento\Framework\View\Result\PageFactory;
    
//use Phpexcel;

//use Phpexcel_IOFactory;

    class Excelbyphp extends \Magento\Framework\App\Action\Action
    {
     /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    private $_testData = [
        ['ID', 'Name', 'Email', 'Group', 'Telephone', '+Telephone', 'ZIP', '0ZIP', 'Country', 'State/Province'],
        [
            1, 'Jon Doe', 'jon.doe@magento.com', 'General', '310-111-1111', '+310-111-1111', 90232, '090232',
            'United States', 'California'
        ],
    ];
    protected $_testHeader = [
        'HeaderID', 'HeaderName', 'HeaderEmail', 'HeaderGroup', 'HeaderPhone', 'Header+Phone',
        'HeaderZIP', 'Header0ZIP', 'HeaderCountry', 'HeaderRegion',
    ];
    protected $_testFooter = [
        'FooterID', 'FooterName', 'FooterEmail', 'FooterGroup', 'FooterPhone', 'Footer+Phone',
        'FooterZIP', 'Footer0ZIP', 'FooterCountry', 'FooterRegion',
    ];

   

   protected $fileFactory;
    protected $purchasereport;
protected $xlsx;
protected $excelFactory;
    public function __construct(
        Context $context,
        \Dckap\PurchaseReport\Block\Purchasereport $purchasereport,        
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        PageFactory $resultPageFactory
    ) {
       
       // $this->excelFactory = $excelFactory;
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
         $output = json_decode($this->purchasereport->getOrdersByProductCategory());
         $fileName = 'd.xlsx'; 
            $convert = new \Magento\Framework\Convert\Excel(new \ArrayIterator($output));
            $content = $convert->convert('single_sheet');
            return $this->fileFactory->create(
            $fileName,
            $content, //content here. it can be null and set later 
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
             'application/octet-stream',
             true );
          
        
        }
    }       