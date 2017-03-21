<?php
    namespace Dckap\PurchaseReport\Controller\Report;
   
    use Magento\Framework\App\Action\Context;
    use Magento\Framework\View\Result\PageFactory;
    

    class Savepdfemail extends \Magento\Framework\App\Action\Action
    {
     /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $_directoryList;
   

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */

   
    protected $purchasereport;

    public function __construct(
        Context $context,
        \Dckap\PurchaseReport\Block\Purchasereport $purchasereport,
        PageFactory $resultPageFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
        
    ) {

         $this->purchasereport = $purchasereport;
        $this->resultPageFactory = $resultPageFactory;
        $root = $directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        $this->_directoryList = $directoryList;
        parent::__construct($context);
    }

    /**
     * Customer order history
     *
     * @return \Magento\Framework\View\Result\Page
     */
        public function execute()
        {
          
if(!empty($_POST['data'])){
    $data = $_POST['data'];
    $fname = "test.pdf"; // name the file
    $file = fopen($root."/pub/media/template/purchasereports/" .$fname, 'w'); // open the file path
    fwrite($file, $data); //save data
    fclose($file);
} else {
    echo "No Data Sent";
} 

         exit();
        }
    }       