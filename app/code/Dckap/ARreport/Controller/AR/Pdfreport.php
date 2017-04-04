<?php
namespace Dckap\ARreport\Controller\AR;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Dckap\ARreport\Block\ARreport;
class Pdfreport extends \Magento\Framework\App\Action\Action
{
     /**
     * @var PageFactory
     */
     protected $resultPageFactory;

     protected $arreport;


    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ARreport $arreport
        ) {


        $this->resultPageFactory = $resultPageFactory;
        $this->arreport = $arreport;
        parent::__construct($context);
    }

    /**
     * Customer order history
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $areports = $this->arreport->getReports();
         // $totals = $this->arreport->getTotalValues($areports);
        
        $reports[0] = array('Invoice No',	'Invoice Date',	'Original Invoice Amount',	'Net Due Date',	'Terms Due Date',	'Total Due',	'Under 30',	'31 to 60','61 to 90',	'Over 90');
        $i = 1;
        foreach ($areports as $key=>$areports)
        {
            $str             =   $areports['invoice_date']; 
            $str             =   substr($str, 0, strrpos($str, 'T')); 
            $net_due_date    =   '-'; //$areports->net_due_date; 
            $net_due_date    =   '-'; //date('m/d/Y', strtotime($areports->net_due_date)) ;
            $terms_due_date  =   '-'; //date('m/d/Y', strtotime($areports->terms_due_date)); 
            
            $reports[$i][]   =   $areports['invoice_no'];
            $reports[$i][]   =   date('m/d/Y', strtotime($areports['invoice_date']));
            $reports[$i][]   =   strip_tags($this->arreport->getFormattedCurrency()->currency($areports['original_amount']));
            $reports[$i][]   =   '-'; //$net_due_date;
            $reports[$i][]   =   '-'; //$terms_due_date;
            $reports[$i][]   =   strip_tags($this->arreport->getFormattedCurrency()->currency($areports['total_due']));
            $reports[$i][]   =   '-'; //$areports->bucket1;
            $reports[$i][]   =   '-'; //$areports->bucket2;
            $reports[$i][]   =   '-'; //$areports->bucket3;
            $reports[$i][]   =   '-'; //$areports->bucket4;
            
            $i++;
        }

        // $reports[$i]=array('','','','','',$totals["total_due"],	$totals["bucket1"],	$totals["bucket2"],$totals["bucket3"],$totals["bucket4"]	);
        echo json_encode($reports);
        exit();

    }
}       