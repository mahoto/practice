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
 * Shopbybrand Resource Model
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Model_Mysql4_Brand extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('shopbybrand/brand', 'brand_id');
    }
    
    public function getCatalogBrand($allStore = false)
	{
		$prefix = Mage::helper('shopbybrand')->getTablePrefix();			
		$attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
		$select = $this->_getReadAdapter()->select()
					->from(array('eao'=> $prefix .'eav_attribute_option'),array('option_id','eaov.value','eaov.store_id'))
					->join(array('ea'=> $prefix .'eav_attribute'),'eao.attribute_id=ea.attribute_id',array())
					->join(array('eaov'=> $prefix .'eav_attribute_option_value'),'eao.option_id=eaov.option_id',array())
					->where('ea.attribute_code=?',$attributeCode);
        if($allStore)
            $select->where('eaov.store_id=?',0);
        else {
            $select->where('eaov.store_id !=?',0);
        }
		$option = $this->_getReadAdapter()->fetchAll($select);
		return $option;	
	}
    
    public function addOption($brand){
        $op = Mage::getModel('eav/entity_attribute_option')->load($brand->getOptionId());
        $prefix = Mage::helper('shopbybrand')->getTablePrefix();			
		$attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        $brandStoreId = 0;
        if($brand->getOptionId()){
            if($brand->getStoreId())
                $brandStoreId = $brand->getStoreId();
            $select = $this->_getReadAdapter()->select()
                ->from(array('eao'=> $prefix .'eav_attribute_option'),array('option_id','eaov.value','eaov.store_id'))
                ->join(array('ea'=> $prefix .'eav_attribute'),'eao.attribute_id=ea.attribute_id',array())
                ->join(array('eaov'=> $prefix .'eav_attribute_option_value'),'eao.option_id=eaov.option_id',array())
                ->where('ea.attribute_code=?',$attributeCode)
                ->where('eao.option_id=?', $brand->getOptionId())
                ->where('eaov.store_id=?', $brandStoreId)
            ;
            $storeValue = $this->_getReadAdapter()->fetchAll($select);
            if(count($storeValue)){
                foreach ($storeValue as $value){
                    if(isset($value['value'])&& $value['value']){
                        if($value['value'] == $brand->getName())
                            return ;
                        else{
                            $data = array(
                                'value' => $brand->getName()
                            );
                            $where= array(
                                'option_id=?' => $brand->getOptionId(),
                                'store_id=?' => $brandStoreId
                            );
                            $update = $this->_getWriteAdapter()->update($prefix.'eav_attribute_option_value', $data, $where);
                        }
                    }
                }
            }else{
                $data = array(
                    'value' => $brand->getName(),
                    'option_id' => $brand->getOptionId(),
                    'store_id' => $brandStoreId
                );
                $update = $this->_getWriteAdapter()->insert($prefix.'eav_attribute_option_value', $data);
            }
        }else{
            $attributeId = Mage::getSingleton('eav/config')
                ->getAttribute('catalog_product', $attributeCode)->getId();
            $setup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup('catalog_setup');
            $option['attribute_id'] = $attributeId;
            if($brand->getStoreId())
                $option['value']['option'][$brand->getStoreId()] = $brand->getName();
            else {
                $option['value']['option'][0] = $brand->getName();
            }
            $setup->addAttributeOption($option);
            //get option id
            $select = $this->_getReadAdapter()->select()
                ->from(array('eao'=> $prefix .'eav_attribute_option'),array('option_id','eaov.value','eaov.store_id'))
                ->join(array('ea'=> $prefix .'eav_attribute'),'eao.attribute_id=ea.attribute_id',array())
                ->join(array('eaov'=> $prefix .'eav_attribute_option_value'),'eao.option_id=eaov.option_id',array())
                ->where('ea.attribute_code=?',$attributeCode)
                ->where('eaov.value=?', $brand->getName())
                ->where('eaov.store_id=?', $brandStoreId)
            ;
            $option = $this->_getReadAdapter()->fetchAll($select);
            if(count($option)){
                $optionId = $option[0]['option_id'];
                return $optionId;
            }
        }
        return null;
    }
    
    public function removeOption($brand){
        $op = Mage::getModel('eav/entity_attribute_option')->load($brand->getOptionId());
        $prefix = Mage::helper('shopbybrand')->getTablePrefix();			
		$attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        $brandStoreId = 0;
        if($brand->getOptionId()){
            if($brand->getStoreId())
                $brandStoreId = $brand->getStoreId();
            $option = Mage::getModel('eav/entity_attribute_option')->load($brand->getOptionId());
            try{
                $option -> delete();
            }  catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
    }
    
    public function getBrandByOption($option)
	{
        $brand = Mage::getModel('shopbybrand/brand')
                    ->setStoreId($option['store_id']);
		if(isset($option['option_id']) && $option['option_id']){
            $brand->load($option['option_id'], 'option_id');
        }
		return $brand;
	}
    
    public function convertData() {
        $data = $this->getOldDataBrand();
        if (!count($data))
            return;
        foreach ($data as $value) {
           
            if ($value['store_id'] == 0) {
                $urlRewrite = Mage::getModel('shopbybrand/urlrewrite')->loadByRequestPath($value['url_key']);
                if($urlRewrite->getId())
                    $urlRewrite->delete();
                $modelBrand = $this->getBrandByName($value['name']);
                $dataBrand = array(
                    'name' => $value['name'],
                    'url_key' => $value['url_key'],
                    'page_title' => $value['page_title'],
                    'image' => $value['image'],
                    'thumbnail_image' => $value['image_thumbnail'],
                    'is_featured' => $value['featured'],
                    'meta_keywords' => $value['meta_keywords'],
                    'meta_description' => $value['meta_description'],
                    'short_description' => $value['description_short'],
                    'description' => $value['description'],
                    'status' => $value['status'],
                    'brand_id' => $modelBrand->getBrandId(),
                    'created_time' => $value['created_time'],
                    'updated_time' => $value['update_time'],
                    'order' => $value['ordering'],
                    'option_id'=>$value['option_id'],
                );
                $modelBrand->setData($dataBrand)->setStoreId($value['store_id']);
                try {
                    
                    $productIds = Mage::helper('shopbybrand/brand')->getProductIdsByBrand($modelBrand);
                    if(is_string($productIds))
                    $modelBrand->setProductIds($productIds)->save();
                    
                    $categoryIds=Mage::helper('shopbybrand/brand')->getCategoryIdsByBrand($modelBrand);
                    if(is_string($categoryIds))
                    $modelBrand->setCategoryIds($categoryIds);
                    
                    
                    $modelBrand->save();
                    $modelBrand->updateUrlKey();
                    if($value['image'])
                    $this->copyImagOldData($modelBrand,'image',$value['image']);
                    if($value['image_thumbnail'])
                    $this->copyImagOldData($modelBrand,'thumbnail_image',$value['image_thumbnail']);
                } catch (Exception $exc) {
//                echo $exc->getTraceAsString();
                }
            } else {
                $modelBrand = $this->getBrandByName($value['name']);
                if (!$value['default_name_store']) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'name', $value['name_store']);
                }
                if ($value['url_key'] && $value['url_key'] != $modelBrand->getUrlKey()) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'url_key', $value['url_key']);
                }
                if (!$value['default_page_title']) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'page_title', $value['page_title']);
                }
                if (!$value['default_image']  && $value['image']) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'image', $value['image']);
                }
                if ($value['image_thumbnail'] && $value['image_thumbnail'] != $modelBrand->getThumbnailImage()) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'thumbnail_image', $value['image_thumbnail']);
                }
                if (!$value['default_featured']) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'is_featured', $value['featured']);
                }
                if (!$value['default_meta_keywords']) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'meta_keywords', $value['meta_keywords']);
                }
                if (!$value['default_meta_description']) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'meta_description', $value['meta_description']);
                }
                if (!$value['default_description_short']) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'short_description', $value['description_short']);
                }
                if (!$value['default_description']) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'description', $value['description']);
                }
                if (!$value['default_status']) {
                    $this->saveStoreValue($modelBrand, $value['store_id'], 'status', $value['status']);
                }
            }
        }
    }

    public function getBrandByName($name) {
        $collection = Mage::getModel('shopbybrand/brand')->getCollection()
                ->addFieldToFilter('name', $name);
        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }
        return Mage::getModel('shopbybrand/brand');
    }

    public function saveStoreValue($brand, $storeId, $attribute, $value) {
        $attributeValue = Mage::getModel('shopbybrand/brandvalue')
                ->loadAttributeValue($brand->getId(), $storeId, $attribute);
        try {
            $this->copyImagOldData($brand,$attribute,$value);
            $attributeValue->setValue($value)
                    ->save();
        } catch (Exception $e) {
            
        }
    }

    public function getOldDataBrand() {
        $prefix = Mage::helper('shopbybrand')->getTablePrefix();
        try {
            $select = $this->_getReadAdapter()->select()
                    ->from(array('manu' => $prefix . 'manufacturer'));
            $data = $this->_getReadAdapter()->fetchAll($select);
        } catch (Exception $exc) {
//            echo $exc->getTraceAsString();
        }
        return $data;
    }
    public function copyImagOldData($brand,$type,$image){
		if($type=='image'){
			Mage::helper('shopbybrand')->createImageFolder($brand->getId());
			$copyfrom = Mage::getBaseDir('media') . DS .'manufacturers' .DS. strtolower(substr($brand->getName(),0,1)).substr(md5($brand->getName()),0,10). Mage::helper('shopbybrand')->refineUrlKey($brand->getName()).DS.$image;
			// $copyto = Mage::getBaseDir('media') . DS .'brands' .DS. strtolower(substr($brand->getName(),0,1)).substr(md5($brand->getName()),0,10). Mage::helper('shopbybrand')->refineUrlKey($brand->getName()).DS.$image;
			$copyto = Mage::getBaseDir('media') . DS .'brands' .DS. $brand->getId().DS.$image;
			copy($copyfrom, $copyto);
			$copyfrom = Mage::getBaseDir('media') . DS .'manufacturers\cache' .DS. strtolower(substr($brand->getName(),0,1)).substr(md5($brand->getName()),0,10). Mage::helper('shopbybrand')->refineUrlKey($brand->getName()).DS.$image;
			// $copyto = Mage::getBaseDir('media') . DS .'brands\cache' .DS. strtolower(substr($brand->getName(),0,1)).substr(md5($brand->getName()),0,10). Mage::helper('shopbybrand')->refineUrlKey($brand->getName()).DS.$image;
            $copyto = Mage::getBaseDir('media') . DS .'brands\cache' .DS.$brand->getId().DS.$image;
			copy($copyfrom, $copyto);
		}
		if($type=='thumbnail_image'){
			Mage::helper('shopbybrand')->createThumbnailImageFolder($brand->getId());
			$copyfrom = Mage::getBaseDir('media') . DS .'manufacturers\thumbnail' .DS. strtolower(substr($brand->getName(),0,1)).substr(md5($brand->getName()),0,10). Mage::helper('shopbybrand')->refineUrlKey($brand->getName()).DS.$image;
			$copyto = Mage::getBaseDir('media') . DS .'brands\thumbnail' .DS. $brand->getId() .DS.$image;
			copy($copyfrom, $copyto);
		}
	}
    
    public function getAttributeOptions($value){
        $prefix = Mage::helper('shopbybrand')->getTablePrefix();			
		$attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
		$select = $this->_getReadAdapter()->select()
					->from(array('eao'=> $prefix .'eav_attribute_option'),array('option_id','eaov.value','eaov.store_id'))
					->join(array('ea'=> $prefix .'eav_attribute'),'eao.attribute_id=ea.attribute_id',array())
					->join(array('eaov'=> $prefix .'eav_attribute_option_value'),'eao.option_id=eaov.option_id',array())
					->where('ea.attribute_code=?',$attributeCode);
        //$select->where('eaov.store_id=?',0);
		$select->where('eaov.value=?',$value);
		$option = $this->_getReadAdapter()->fetchAll($select);
		return $option;	
    }
}