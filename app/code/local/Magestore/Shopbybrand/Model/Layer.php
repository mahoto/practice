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
 * Layer Model
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Model_Layer extends Mage_Catalog_Model_Layer {

    public function getProductCollection($brandId = null) {
        if (isset($this->_productCollections[$this->getCurrentCategory()->getId()])) {
            $collection = $this->_productCollections[$this->getCurrentCategory()->getId()];
        } else {
            if(!$brandId)
            $brandId = Mage::app()->getRequest()->getParam("id");
            $brand = Mage::getModel('shopbybrand/brand')->setStoreId(Mage::app()->getStore()->getId())
                    ->load($brandId);
            $productIds = explode(',', $brand->getData('product_ids'));
            $collection = Mage::getModel('catalog/product')
                    ->getCollection()
                    //->addAttributeToSelect('*')
                    ;
            if($brand->getId())
                $collection->addAttributeToFilter('entity_id', array('in'=>$productIds));
            $this->prepareProductCollection($collection);
            $this->_productCollections[$this->getCurrentCategory()->getId()] = $collection;
        }

        return $collection;
    }
    
    public function prepareProductCollection($collection)
    {
        $brandId = Mage::app()->getRequest()->getParam('id');
        if($brandId)
            $collection
                ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addTaxPercents()
                ->addUrlRewrite($this->getCurrentCategory()->getId())
                ;
		
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
        return $this;
    }

}