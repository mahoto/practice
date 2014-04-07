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
class Magestore_Shopbybrand_Block_Ajaxupdate extends Mage_Core_Block_Template {

    /**
     * prepare block's layout
     *
     * @return Magestore_Shopbybrand_Block_Shopbybrand
     */
    public function __construct() {
        parent::__construct();
        $this->setTemplate('shopbybrand/ajaxupdate.phtml');
    }

    public function _prepareLayout() {
        return parent::_prepareLayout();
    }

    public function getBrandsByCategory() {
        $categoryId = $this->getRequest()->getParam('category');
        if ($categoryId == 'top_signup') {
            $brands = Mage::helper('shopbybrand/brand')->getMostSubscriber();
            return $brands;
        }
        if ($categoryId == 'top_sell') {
            $brands = Mage::helper('shopbybrand/brand')->getBrandTopSeller();
            return $brands;
        }
        if ($categoryId == 'top_new') {
            $brands = Mage::helper('shopbybrand/brand')->getBrandTopNew();
            return $brands;
        }
        if ($categoryId == 'top_product') {
            $brands = Mage::helper('shopbybrand/brand')->getBrandTopProduct();
            return $brands;
        }
		$brands = Mage::helper('shopbybrand/brand')->getBrandProduct();
        $brands ->addFieldToFilter('category_ids', array('finset' => $categoryId));
        return $brands;
    }

    public function getBrandUrl($brand) {
        $categoryId = $this->getRequest()->getParam('category');
        if (is_numeric($categoryId)) {
            $url = $this->getUrl($brand->getUrlKey()) . '?cat=' . $categoryId;
        } else {
            $url = $this->getUrl($brand->getUrlKey());
        }
        return $url;
    }

    public function getBrandImage($brand, $width) {
        if ($brand->getImage()) {
            $file = Mage::getBaseUrl('media') . 'brands/' . strtolower(substr($brand->getName(), 0, 1)) . substr(md5($brand->getName()), 0, 10) . Mage::helper('shopbybrand')->refineUrlKey($brand->getName()) . '/' . $brand->getImage();
        } else {
            $file = Mage::getBaseUrl('media') . 'brands/no-image.png';
        }
        if ($file) {
            $img = "<img  src='" . $file . "' title='" . $brand->getName() . "' width=" . $width . " border='0'/>";
            return $img;
        } else {
            return NULL;
        }
    }

    public function getStoreId() {
        $storeId = Mage::app()->getStore()->getId();
        return $storeId;
    }

    public function displayBrandImage() {
        $store = Mage::app()->getStore()->getId();
        $display = Mage::getStoreConfig('shopbybrand/optional/display_brand_image', $store);
        return $display;
    }

}