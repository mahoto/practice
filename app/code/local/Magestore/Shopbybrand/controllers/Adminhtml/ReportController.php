<?php
/**
 * Magestore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */

/**
 * Shopbybrand Adminhtml Controller
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Adminhtml_ReportController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('shopbybrand/report')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Report Brand'), Mage::helper('adminhtml')->__('Report Brand'));
		return $this;
	}
 
	public function indexAction() {
       
		$this->_title($this->__('Report Brand'))
			->_title($this->__('Report Brand'));
		$this->_initAction();
        $this->_addContent($this->getLayout()->createBlock('shopbybrand/adminhtml_report_statistic'));
        $this->renderLayout();
	}
    public function exportCsvAction(){
        
		$fileName   = 'brand_report_sales.csv';
		$content	= $this->getLayout()->createBlock('shopbybrand/adminhtml_report_statistic_grid')->getCsv();
		$this->_prepareDownloadResponse($fileName,$content);
	}

	public function exportXmlAction(){
        
		$fileName   = 'brand_report_sales.xml';
		$content	= $this->getLayout()->createBlock('shopbybrand/adminhtml_report_statistic_grid')->getXml();
		$this->_prepareDownloadResponse($fileName,$content);
	}
    public function exportSalesExcelAction()
    {
        
        $fileName   = 'brand_report_sales_excel.xml';
		$content	= $this->getLayout()->createBlock('shopbybrand/adminhtml_report_statistic_grid')->getExcelFile();
		$this->_prepareDownloadResponse($fileName,$content);
    }
    
    protected function _licenseKeyError() {
        return false;
        
    }
}