<?php
namespace Dckap\Requestquote\Controller\Index;
use Magento\Framework\App\Filesystem\DirectoryList;


class Post extends \Dckap\Requestquote\Controller\Index
{  
    public function execute()
    {

        $request_post = $this->getRequest()->getPostValue();
        if (count(array_filter($request_post['pid'])) > 0) {
            $guestname = '';
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customerSession = $objectManager->get('Magento\Customer\Model\Session');
            $customer_address = $objectManager->get('Magento\Customer\Model\Address');
            if($customerSession->getCustomer()->getId()){
                $post['contact_name'] = $customerSession->getCustomer()->getName();
                $post['customer_id'] = $customerSession->getCustomer()->getId();
                $post['email'] = $customerSession->getCustomer()->getEmail();
                $customerAddressId = $customerSession->getCustomer()->getDefaultBilling();
                if ($customerAddressId){
                    $address = $customer_address->load($customerAddressId);
                    $cust_data = $address->getData();
                    $post['company'] = $cust_data['company'];
                    $post['phone'] = $cust_data['telephone'];
                }
            }else{
                if($request_post['req_name']){
                    $websiteId = $objectManager->get('\Magento\Store\Model\StoreManagerInterface')->getWebsite()->getWebsiteId();
                    $customerObj = $objectManager->create('Magento\Customer\Model\Customer')
                    ->setWebsiteId($websiteId)
                    ->loadByEmail(trim($request_post['req_email']));
                    if(empty($customerObj->getId())){
                        $post['contact_name'] = $request_post['req_name'];
                        $post['customer_id'] = 0;
                        $post['email'] = $request_post['req_email'];
                        $post['company'] = $request_post['req_company'];
                        $post['phone'] = $request_post['req_phone'];
                    }else{
                        $this->messageManager->addError(__(' Email Address already available in our database.'));
                        $this->_redirect('requestquote/index');
                        return;
                    }
                }else{
                    $this->messageManager->addError(__('You have been logged out. Please log in again.'));
                    $this->_redirect('requestquote/index');
                    return;  
                }
            }
            $post['zipcode'] = $request_post['req_zipcode'];
            $this->inlineTranslation->suspend();

            if(!$post){

                $this->__redirect('*/*/');
                return;
            }

            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($post);
            $error = false;

            if (!\Zend_Validate::is(trim($post['contact_name']), 'NotEmpty')) {
                $error = true;
            }
            if (!\Zend_Validate::is(trim($post['email']), 'EmailAddress')) {
                $error = true;
            }

            if ($error) {
                throw new \Exception();
            }

            /*Email Sending Start*/

            /*sender email info*/
            $email_from = $this->scopeConfig->getValue(
                'trans_email/ident_support/email',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );

            $full_name = $this->scopeConfig->getValue(
                'trans_email/ident_support/name',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
            $quotedetails ='';
            for($p=0; $p<count($request_post['pid']); $p++){
                if($request_post['pid'][$p] != ""){
                    $quotedetails .= "<tr><td style='padding:10px'>".$request_post['req_product'][$p]."</td><td style='padding:10px'>".$request_post['req_qty'][$p]."</td></tr>";
                }
            }
            $to = $post['email'];
            $subject = "Request Quote";
            $message = "<html><head><title>Request Quote.</title></head><body><table style='width:100%'><tr><td style='text-align: left; margin-bottom: 10px;display: inline-flex;'>Hello ".$post['contact_name'].",</td></tr><tr><td>A new RFQ has been submitted with the following details. The details are as follows.</td></tr><tr><td><table style='border: 1px solid #ececec; width:100%'><tr style='background-color: #ececec;padding: 10px;text-align: left;
            '><th style='padding:10px'>Product Name</th><th style='padding:10px'>QTY</th></tr>".$quotedetails."</table><td></tr></table></body></html>";

            $email_from = $full_name.'<'.$email_from.'>';
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: '.$email_from . "\r\n";
            $chk = mail($to,$subject,$message,$headers); 

            /*Email Sending End*/

            /*Save Data Start*/

            $post['create_date'] = time();
            $post['update_date'] = time();
            $model = $this->_objectManager->create('Dckap\Requestquote\Model\Requestforquote');
            $model->setData($post);

            try{        

                $rfq = $model->save();
                $quoteid = $rfq->getRequestquoteId();

                for($i=0; $i<count($request_post['pid']); $i++){
                    if($request_post['pid'][$i] != ''){
                        $request_model = $this->_objectManager->create('Dckap\Requestquote\Model\Requestquote');
                        $request_model->setRequestquoteId($quoteid);
                        $request_model->setProductId($request_post['pid'][$i]);
                        $request_model->setProductName($request_post['req_product'][$i]);
                        $request_model->setProductQty($request_post['req_qty'][$i]);
                        $request_model->setCreateDate(time());
                        $request_model->setUpdateDate(time());                       
                        $request_model->save();
                    }
                }
                /*Save Data End*/


                $this->messageManager->addSuccess(
                    __('Thanks for contacting us with your quote request. We\'ll respond to you very soon.')
                    );

                $this->_redirect('requestquote/index');
                return;
            } catch (\Exception $e) {

                $this->inlineTranslation->resume();
                $this->messageManager->addError(
                    __($e->getMessage().' We can\'t process your request right now. Sorry, that\'s all we know.')
                    );
                $this->_redirect('requestquote/index');
                return;
            }
        }
        else{
            $this->messageManager->addError(
                __('There is no product selected for Quote submission.')
                );
            $this->_redirect('requestquote/index');
            return;  
        }                         
    }
}