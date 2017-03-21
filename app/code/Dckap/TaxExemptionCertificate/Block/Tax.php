<?php
namespace Dckap\TaxExemptionCertificate\Block;
class Tax extends \Magento\Framework\View\Element\Template 
{
   
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Filesystem\DirectoryList $dir,    
        array $data = []
    ) {
        $this->_dir = $dir;
        parent::__construct($context, $data);
    }

}