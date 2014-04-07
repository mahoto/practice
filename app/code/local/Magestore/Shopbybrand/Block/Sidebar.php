<?php

class Magestore_Shopbybrand_Block_Sidebar extends Mage_Core_Block_Template {

     public function getBrandsByBegin() {
        if ($input = $this->getInputSearch()) {

            $store = Mage::app()->getStore()->getId();
            $extended_search = Mage::getStoreConfig('shopbybrand/general/brand_extended_search', $store);
            $shopbybrands = Mage::getModel('shopbybrand/brand')->getCollection();
            $shopbybrands->setStoreId($this->getStoreId());
            $shopbybrands->addFieldToFilter('name', array('like' => '%' . $input . '%'));
            if ($extended_search) {
                $allIds1 = $shopbybrands->getAllIDs();
                $allIds2 = Mage::getModel('shopbybrand/brand')
                        ->getCollection()
                        ->setStoreId($this->getStoreId())
                        ->addFieldToFilter('description', array('like' => '%' . $input . '%'))
                        ->getAllIDs();
                $allIds = array_merge($allIds1, $allIds2);
                $allIds = array_unique($allIds);
                $shopbybrands = Mage::getModel('shopbybrand/brand')
                        ->getCollection()
                        ->setStoreId($this->getStoreId())
                        ->addFieldToFilter('brand_id', array('in' => $allIds));
            }
            return $shopbybrands;
        }
        if ($top = $this->getRequest()->getParam("top")) {
            if ($top == 'most_subscribers') {
                
            } elseif ($top == 'sales') {
                
            }
        }
        $begin = $this->getRequest()->getParam("begin");
        $shopbybrands = Mage::helper("shopbybrand")->getBrandsByBegin($begin);

        return $shopbybrands;
    }
    public function getDisplaySidebar(){
        $store = Mage::app()->getStore()->getId();
        $display = Mage::getStoreConfig('shopbybrand/optional/display_sidebar', $store);
        return $display;
    }
    public function getMaximumSidebar(){
        $store = Mage::app()->getStore()->getId();
        $display = Mage::getStoreConfig('shopbybrand/optional/maximum_item_sidebar', $store);
        return $display;
    }
    public function getBrandUrl($brand) {
        $url = $this->getUrl($brand->getUrlKey(), array());
        return $url;
    }
    public function getDisplayModule(){
        $store = Mage::app()->getStore()->getId();
        $display = Mage::getStoreConfig('shopbybrand/general/enable', $store);
        return $display;
    }
}
