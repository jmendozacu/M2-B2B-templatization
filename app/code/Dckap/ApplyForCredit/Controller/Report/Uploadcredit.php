<?php
/**
*
* Copyright © 2016 Magento. All rights reserved.
* See COPYING.txt for license details.
*/
namespace Dckap\ApplyForCredit\Controller\Report;

class Uploadcredit extends \Magento\Framework\App\Action\Action 
{
    /**
    * Configuration paths
    */
    const XML_PATH_COPY_TO_ACCOUNTING = 'sales_email/dckap_accounting/copy_to_accounting';

    private $uploader;
    private $filesystem;
    protected $messageManager;
    protected $customersession;
    protected $resultfactory;
    protected $_objectManager = null;


    public function __construct(
        \Magento\MediaStorage\Model\File\UploaderFactory $uploader,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Message\ManagerInterface $messageManager, 
        \Magento\Customer\Model\Session $customersession,
        \Magento\Framework\Controller\ResultFactory $resultfactory,
        \Magento\Framework\ObjectManagerInterface $objectManager, 

        \Magento\Framework\App\Action\Context $context

        ) 
    { 
        $this->uploader = $uploader; 
        $this->filesystem = $filesystem;
        $this->messageManager = $messageManager;
        $this->customersession = $customersession;
        $this->resultFactory = $resultfactory;
        $this->_objectManager = $objectManager;

        parent::__construct($context);
    }  

    public function execute()
    {
        $files =$this->getRequest()->getFiles() ;
        $params = $this->getRequest()->getParams();
        try {
            if($files['fileToUpload']['name'] == '')
            {
                $this->messageManager->addError("Please upload a pdf file");
                return $this->resultRedirectFactory->create()->setPath('credit/report/applycredit/');
            }
            if($files['fileToUpload']['type'] != 'application/pdf')
            {
                $this->messageManager->addError("Please upload a pdf file");
                return $this->resultRedirectFactory->create()->setPath('credit/report/applycredit/');
            }
            $customer_id = '';
            if($this->customersession->isLoggedIn()) {  
                $customer_id =  $this->customersession->getCustomerId();
                $base_media_path = 'dckap/customer_credit_line';
                $uploader = $this->uploader->create(
                    ['fileId' => 'fileToUpload']
                    );
                $uploader->setAllowedExtensions(['pdf']);                    
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);

                $mediaDirectory = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

                $result = $uploader->save($mediaDirectory->getAbsolutePath($base_media_path));

                $credits = $this->_objectManager->create('Dckap\ApplyForCredit\Model\Credit');


                $blocks = $this->_objectManager->create('Dckap\ApplyForCredit\Block\Credit');
                $collection = $blocks->getCreditCollections();

                if(!empty($collection))
                {   
                    $credit_files_db = array();
                    $get_credit_data = $this->_objectManager->get('Dckap\ApplyForCredit\Model\Credit')->load($collection['id']);
                    $credit_files_db = json_decode($collection['credit_file'],true);  

                    $count = count($credit_files_db);                      
                    $credit_files_db1 [$count] = $result['file']; 
                    $obj_merged =  array_merge( $credit_files_db, $credit_files_db1);
                    $filename = json_encode($obj_merged);
                    $get_credit_data->setCreditFile("$filename");
                    $get_credit_data->save();
                }else
                {
                    $credit_files_upload[0] = $result['file'];
                    $filename = json_encode($credit_files_upload);
                    $credits->setCustomerId($customer_id);
                    $credits->setCreditFile("$filename");
                    $credits->save();

                }
                /* sending email to Accounting */
                $senderInfo = [
                'name' => $blocks->getEmailCopyTo('trans_email/ident_support/name'),
                'email' => $blocks->getEmailCopyTo('trans_email/ident_support/email')
                ];

                $receiverInfo_email = $blocks->getEmailCopyTo('sales_email/dckap_accounting/copy_to_accounting');
                $receiverInfo=[
                'name' => 'Accounting',
                'email' => $receiverInfo_email,
                ];
                $pdfFile = 'pub/media/dckap/customer_credit_line'.$result['file'];
                $postObject = array();
                $customer_name =  $this->customersession->getCustomer()->getName();
                $p21customer_id = $blocks->p21customerid();

                $postObject = ['customer_id' => $p21customer_id, 'customer_name' => $customer_name];

                $blocks->sendEmailTemplate($postObject,$senderInfo,$receiverInfo,$pdfFile);
                /* Email sent to Accounting*/

                $this->messageManager->addSuccess("Uploaded Successfully");
                return $this->resultRedirectFactory->create()->setPath('credit/report/applycredit/');
            }
        }
        catch (\Magento\Framework\Exception\LocalizedException $e) {

            $this->messageManager->addError($e->getMessage());
            return $this->resultRedirectFactory->create()->setPath('credit/report/applycredit/');
        }
    }
}
