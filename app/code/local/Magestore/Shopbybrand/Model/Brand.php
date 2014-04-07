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
 * Shopbybrand Model
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_Model_Brand extends Mage_Core_Model_Abstract {

    protected $_storeId = null;
    protected $_productIds = array();

    public function getStoreId() {
        return $this->_storeId;
    }

    public function setStoreId($storeId) {
        $this->_storeId = $storeId;
        return $this;
    }

    public function getStoreAttributes() {
        return array(
            'name',
            'is_featured',
            'page_title',
            'meta_keywords',
            'meta_description',
            'short_description',
            'description',
            'status'
        );
    }

    public function load($id, $field = null) {
        parent::load($id, $field);
        if ($this->getStoreId()) {
            $this->loadStoreValue();
        }
        return $this;
    }

    public function loadStoreValue($storeId = null) {
        if (!$storeId)
            $storeId = $this->getStoreId();
        if (!$storeId)
            return $this;
        $storeValues = Mage::getModel('shopbybrand/brandvalue')->getCollection()
                ->addFieldToFilter('brand_id', $this->getId())
                ->addFieldToFilter('store_id', $storeId);
        foreach ($storeValues as $value) {
            $this->setData($value->getAttributeCode() . '_in_store', true);
            $this->setData($value->getAttributeCode(), $value->getValue());
        }

        return $this;
    }

    protected function _beforeSave() {
        if ($storeId = $this->getStoreId()) {
            $defaultBrand = Mage::getModel('shopbybrand/brand')->load($this->getId());
            $storeAttributes = $this->getStoreAttributes();
            foreach ($storeAttributes as $attribute) {
                if ($this->getData($attribute . '_default')) {
                    $this->setData($attribute . '_in_store', false);
                } else {
                    $this->setData($attribute . '_in_store', true);
                    $this->setData($attribute . '_value', $this->getData($attribute));
                }
                $this->setData($attribute, $defaultBrand->getData($attribute));
            }
        }
        return parent::_beforeSave();
    }

    protected function _afterSave() {
        if ($storeId = $this->getStoreId()) {
            $storeAttributes = $this->getStoreAttributes();
            foreach ($storeAttributes as $attribute) {
                $attributeValue = Mage::getModel('shopbybrand/brandvalue')
                        ->loadAttributeValue($this->getId(), $storeId, $attribute);
                if ($this->getData($attribute . '_in_store')) {
                    try {
                        $attributeValue->setValue($this->getData($attribute . '_value'))
                                ->save();
                    } catch (Exception $e) {
                        
                    }
                } elseif ($attributeValue && $attributeValue->getId()) {
                    try {
                        $attributeValue->delete();
                    } catch (Exception $e) {
                        
                    }
                }
            }
        }
        return parent::_afterSave();
    }

    public function _construct() {
        parent::_construct();
        if ($storeId = Mage::app()->getStore()->getId()) {
            $this->setStoreId($storeId);
        }
        $this->_init('shopbybrand/brand');
    }

    public function updateUrlKey() {
        $id = $this->getId();
        $url_key = $this->getData('url_key');

        try {
            if ($this->getStoreId()) {
                $urlrewrite = Mage::getModel("shopbybrand/urlrewrite")->loadByIdpath("brand/" . $id, $this->getStoreId());

                $urlrewrite->setData("id_path", "brand/" . $id);
                $urlrewrite->setData("request_path", $this->getData('url_key'));

                $urlrewrite->setData("target_path", 'brand/index/view/id/' . $id);
                $urlrewrite->setData("store_id", $this->getStoreId());
                try {
                    $urlrewrite->save();
                } catch (Exception $e) {
                    
                }
            } else {
                $stores = Mage::getModel('core/store')->getCollection()
                        ->addFieldToFilter('is_active', 1)
                        ->addFieldToFilter('store_id', array('neq' => 0))
                ;
                foreach ($stores as $store) {
                    $rewrite = Mage::getModel("shopbybrand/urlrewrite")->loadByIdpath("brand/" . $id, $store->getId());
                    $rewrite->setData("id_path", "brand/" . $id);
                    $rewrite->setData("request_path", $this->getData('url_key'));
                    $rewrite->setData("target_path", 'brand/index/view/id/' . $id);
                    try {
                        $rewrite->setData('store_id', $store->getId())
                                ->save()
                        ;
                    } catch (Exception $e) {
                        
                    }
                }
            }
        } catch (Exception $e) {
            $this->delete();
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }

    public function getProductIds() {
        if (count($this->_productIds) == 0) {
            if ($this->getId()) {
                $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
                $optionId = $this->getOptionId();
                $collection = Mage::getModel('catalog/product')
                        ->getCollection()
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter($attributeCode, $optionId);
                $this->_productIds = $collection->getAllIds();
            }
        }
        return $this->_productIds;
    }

    public function getSubscriberIds() {
        $subIds = array(0);
        if ($this->getId()) {
            $brandSubscribers = Mage::getModel('shopbybrand/brandsubscriber')
                    ->getCollection()
                    ->addFieldToFilter('brand_id', $this->getId())
                    ->getAllSubscriberIds()
            ;
            $subIds = array_unique($brandSubscribers);
        }
        return $subIds;
    }

    public function deleteUrlRewrite() {
        if ($this->getId()) {
            $stores = Mage::getModel('core/store')->getCollection()
                    ->addFieldToFilter('is_active', 1)
            ;
            foreach ($stores as $store) {
                $url = Mage::getModel('shopbybrand/urlrewrite')->loadByIdPath('brand/' . $this->getId(), $store->getId());
                if ($url->getId()) {
                    $url->delete();
                }
            }
        }
    }

}