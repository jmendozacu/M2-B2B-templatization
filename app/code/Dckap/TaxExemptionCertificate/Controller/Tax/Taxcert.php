<?php
    namespace Dckap\TaxExemptionCertificate\Controller\Tax;
   
    use Magento\Framework\App\Action\Context;
    use Magento\Framework\View\Result\PageFactory;
    use Magento\MediaStorage\Model\File\UploaderFactory;
    use Magento\Framework\Filesystem;
    use Magento\Customer\Model\Session;

    
    class Taxcert extends \Magento\Framework\App\Action\Action
    {
     /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    private $uploader;
    private $filesystem;
    private $customerSession;

    /**
    * Recipient email config path
    */
    const XML_PATH_EMAIL_RECIPIENT = 'contact/email/recipient_email';
    /**
    * @var \Magento\Framework\Mail\Template\TransportBuilder
    */
    protected $_transportBuilder;

    /**
    * @var \Magento\Framework\Translate\Inline\StateInterface
    */
    protected $inlineTranslation;

    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $scopeConfig;

    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $storeManager; 
    /**
    * @var \Magento\Framework\Escaper
    */
    protected $_escaper;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Filesystem $filesystem,
        Session $customerSession,
        UploaderFactory $uploader,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Dckap\TaxExemptionCertificate\Model\Mail\TransportBuilder $transportBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Escaper $escaper
        ) 
       {
          $this->uploader = $uploader; 
          $this->filesystem = $filesystem;
          $this->_customerSession = $customerSession;
          $this->resultPageFactory = $resultPageFactory;
          $this->_transportBuilder = $transportBuilder;
          $this->inlineTranslation = $inlineTranslation;
          $this->scopeConfig = $scopeConfig;
          $this->storeManager = $storeManager;
          $this->_escaper = $escaper;

          parent::__construct($context);
        }
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $path = $storeManager->getStore()->getBaseUrl();
        $m_url=$storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $media_path='pub/media/customer/taxcertificates/';
        $absolue_path = $_SERVER['DOCUMENT_ROOT']."/pub/media/";

        $mediaDirectory = $objectManager->get('Magento\Framework\Filesystem') ->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

        $id = $this->_customerSession->getCustomer()->getId();

        if($_FILES['fileToUpload']['type'] != 'application/pdf')
            {
                 $this->messageManager->addError("Please upload a pdf file.");
                $this->_redirect('taxexemptioncertificate/tax/taxupload');
                return;
            }

if (!file_exists($mediaDirectory->getAbsolutePath("customer"))) {
    mkdir($mediaDirectory->getAbsolutePath("customer"), 0777, true);
    exec("chown -R dckap:www-data ".BP . $mediaDirectory->getAbsolutePath("customer"));
}  
if (!file_exists($mediaDirectory->getAbsolutePath("customer/taxcertificates"))) {
    mkdir($mediaDirectory->getAbsolutePath("customer/taxcertificates"), 0777, true);
    exec("chown -R dckap:www-data ".BP . $mediaDirectory->getAbsolutePath("customer/taxcertificates"));
}
if (!file_exists($mediaDirectory->getAbsolutePath("customer/taxcertificates/".$id))) {
    mkdir($mediaDirectory->getAbsolutePath("customer/taxcertificates/".$id), 0777, true);
    exec("chown -R dckap:www-data ".BP . $mediaDirectory->getAbsolutePath("customer/taxcertificates/".$id));
}        
        try {
               if (isset($_FILES['fileToUpload']) && isset($_FILES['fileToUpload']['name']) && strlen($_FILES['fileToUpload']['name'])) 
                {
                  $request_post = $this->getRequest()->getPostValue();
                  $state = $request_post["taxcert"];
                    $base_media_path = "customer/taxcertificates/".$id;
                    $uploader = $this->uploader->create(
                    ['fileId' => 'fileToUpload']
                    );
                    $uploader->setAllowedExtensions(['pdf']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setFilesDispersion(false);
                    $mediaDirectory = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
                    $result = $uploader->save($mediaDirectory->getAbsolutePath($base_media_path));
                    $filename = $uploader->getUploadedFileName();
                        $taxcert_model = $objectManager->create('Dckap\TaxExemptionCertificate\Model\TaxExemptionCertificate');
                            $taxcert_model->setCustomerId($id);
                            $taxcert_model->setStateName($state);
                            $taxcert_model->setFileName($filename);
                            $taxcert_model->setCreateDate(time());
                            $taxcert_model->setUpdateDate(time());  
                            try{                    
                            $taxcert_model->save();

                            //Email functionality
                            $email = $this->_customerSession->getCustomer()->getData('email'); 
                            $name = $this->_customerSession->getCustomer()->getData('firstname');

                            $post_data['clientemail'] = $name;
             
                            $this->inlineTranslation->suspend();
                            try {
                            $postObject = new \Magento\Framework\DataObject();
                            $postObject->setData($post_data);
                            $error = false;
                            $receiver = [
                            'name' => $this->scopeConfig->getValue('trans_email/ident_sales/name',
                                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                            'email' => $this->scopeConfig->getValue('trans_email/ident_sales/email',
                                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                            ];
                            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE; 
                            $transport = $this->_transportBuilder->setTemplateIdentifier('taxcertificate_email_template') // this code we have mentioned in the email_templates.xml
                            ->setTemplateOptions(
                            [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                            ])
                            ->setTemplateVars(['data' => $postObject])
                            ->setFrom($email)
                            ->addTo($receiver)
                            ->addAttachment(file_get_contents($absolue_path.$base_media_path."/".$filename),'tax_exception_certificate')
                            ->getTransport();
                            $transport->sendMessage(); 
                            $this->inlineTranslation->resume();
                            } catch (\Exception $e) {
                              $this->messageManager->addError($e->getMessage());
                            } 







                            $this->messageManager->addSuccess(__('Tax Exemption Certification file has been updated successfully.'));
                            $this->_redirect('taxexemptioncertificate/tax/taxupload');
                          }catch (\Exception $e){
                            $this->messageManager->addError($e->getMessage());
                            $this->_redirect('taxexemptioncertificate/tax/taxupload');
                          }
                }
            }
            catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('taxexemptioncertificate/tax/taxupload');
                        return;
            }
      
        }
    }       