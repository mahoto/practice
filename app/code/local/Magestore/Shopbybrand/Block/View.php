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
 * Shopbybrand Block
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Block_View extends Mage_Core_Block_Template
{
    /**
     * prepare block's layout
     *
     * @return Magestore_Shopbybrand_Block_Shopbybrand
     */
    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
    
    public function getStoreId(){
        $storeId = Mage::app()->getStore()->getId();
        return $storeId;
    }
    
    public function getBrand(){
        if(!$this->hasData('current_brand')){
            $brandId = $this->getRequest()->getParam('id');
            
            $storeId = $this->getStoreId();
            $brand = Mage::getModel('shopbybrand/brand')->setStoreId($storeId)
                    ->load($brandId);
            $this->setData('current_brand', $brand);
        }
        return $this->getData('current_brand');
    }
    
    public function getBrandImageUrl(){
        $brand = $this->getBrand();
        if($brand->getImage())
		{
			$url = Mage::helper('shopbybrand')->getUrlImage($brand->getId()) .'/'. $brand->getImage();
		
			$img = "<img  src='". $url . "' title='". $brand->getName()."' border='0' align='left' style='width:128px;'/>";
		
			return $img;
		} else{
			return null;
		}
    }
    
    public function getProductListHtml()
    {
        return $this->getChildHtml('search_result_list');
    }
    
    public function setListCollection() {
		$this->getChild('search_result_list')
           ->setCollection($this->_getProductCollection());
    }
    public function setReivewCollection(){
        $this->getChild('shopbybrand-review')
           ->setCollection($this->_getProductCollection());
    }

    protected function _getProductCollection(){
        return $this->getSearchModel()->getProductCollection();
    }
    public function getSearchModel()
    {
        return Mage::getSingleton('shopbybrand/layer');
    }
    public function getCharSearchUrl($begin) {
        $setlink = Mage::getStoreConfig('shopbybrand/general/router');
        $lists = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'W', 'U', 'V', 'X', 'Y', 'Z');
        if (!in_array($begin, $lists)) {
            return $url = $this->getUrl($setlink, array());
        }
        return $this->getUrl($setlink . "/index/index/begin/" . $begin).'#shopbybrand_char_filter';
    }
    public function displayBrandsearch(){
        $store = Mage::app()->getStore()->getId();
        $display=Mage::getStoreConfig('shopbybrand/optional/display_brand_view_search',$store);
        return  $display;
    }
    public function displayBrandSignup(){
        $store = Mage::app()->getStore()->getId();
        $display=Mage::getStoreConfig('shopbybrand/optional/display_brand_signup',$store);
        return  $display;
    }
    public function displayShopby(){
        $store = Mage::app()->getStore()->getId();
        $display=Mage::getStoreConfig('shopbybrand/optional/display_shopby',$store);
        return  $display;
    }
    public function displayBrandCategories(){
        $store = Mage::app()->getStore()->getId();
        $display=Mage::getStoreConfig('shopbybrand/optional/display_brand_categories',$store);
        return  $display;
    }
    public function getBrandCategories() {
        $catids = $this->getBrand()->getCategoryIds();
        $catids =  explode(",", $catids);
        $categories = Mage::getModel('catalog/category')->getCollection()
                ->setStoreId($this->getStoreId())
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id',array('in'=>$catids));
        return $categories;
    }
    
    public function getParentCategories(){
        $catids = $this->getBrand()->getCategoryIds();
        $catids =  explode(",", $catids);
        return Mage::helper('shopbybrand/brand')->getParentCategories($catids);
    }
    
}