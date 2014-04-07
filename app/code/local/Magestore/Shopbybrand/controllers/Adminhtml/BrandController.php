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
class Magestore_Shopbybrand_Adminhtml_BrandController extends Mage_Adminhtml_Controller_Action {

    /**
     * init layout and set active for current menu
     *
     * @return Magestore_Shopbybrand_Adminhtml_ShopbybrandController
     */
    protected function _initAction() {
        //Mage::helper('shopbybrand/brand')->updateBrandsFormCatalog();
        $this->loadLayout()
                ->_setActiveMenu('shopbybrand/shopbybrand')
                ->_addBreadcrumb(
                        Mage::helper('adminhtml')->__('Brands Manager'), Mage::helper('adminhtml')->__('Brand Manager')
        );
        return $this;
    }

    /**
     * index action
     */
    public function indexAction() {
       
        $this->_initAction()
                ->renderLayout();
    }

    /**
     * view and edit item action
     */
    public function editAction() {
        
        $brandId = $this->getRequest()->getParam('id');
        $store = $this->getRequest()->getParam('store');
        $model = Mage::getModel('shopbybrand/brand')->setStoreId($store)
                ->load($brandId);
        Mage::helper('shopbybrand/brand')->getCategoryIdsByBrand($model);
        if ($model->getId() || $shopbybrandId == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }
            Mage::register('brand_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('shopbybrand/shopbybrand');

            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager')
            );
            $this->_addBreadcrumb(
                    Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News')
            );

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('shopbybrand/adminhtml_brand_edit'))
                    ->_addLeft($this->getLayout()->createBlock('shopbybrand/adminhtml_brand_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('shopbybrand')->__('Brand does not exist')
            );
            $this->_redirect('*/*/');
        }
    }

    public function newAction() {
        $this->_forward('edit');
    }

    /**
     * save item action
     */
    public function saveAction() {
        
        if ($data = $this->getRequest()->getPost()) {
            $store = $this->getRequest()->getParam('store',0);
            //if (!$this->getRequest()->getParam('id')) {
                if (isset($data['url_key'])) {
                    $data['url_key'] = Mage::helper('shopbybrand')->refineUrlKey($data['url_key']);
                    $urlRewrite = Mage::getModel('shopbybrand/urlrewrite')->loadByRequestPath($data['url_key'], $store);
                    
                    if ($urlRewrite->getId()) {
                        if(!$this->getRequest()->getParam('id')){
                            Mage::getSingleton('adminhtml/session')->addError('Url key has existed. Please fill out a valid one.');
                            $this->_redirect('*/*/new', array('store' => $store));
                            return;
                        }elseif($this->getRequest()->getParam('id') && $urlRewrite->getIdPath() != 'brand/'.$this->getRequest()->getParam('id')){
                            Mage::getSingleton('adminhtml/session')->addError('Url key has existed. Please fill out a valid one.');
                            $this->_redirect('*/*/edit', array('store' => $store,'id'=>$this->getRequest()->getParam('id')));
                            return;
                        }
                    }
                }
            //}
            if (isset($data['image']['delete'])) {
                Mage::helper('shopbybrand')->deleteImageFile($data['name'], $data['old_image']);
                unset($data['old_image']);
            }
            $data['image'] = "";
            if (isset($_FILES['image']))
                $data['image'] = Mage::helper('shopbybrand')->refineImageName($_FILES['image']['name']);

            if (!$data['image'] && isset($data['old_image'])) {
                $data['image'] = $data['old_image'];
            }
            if (isset($data['thumbnail_image']['delete'])) {

                Mage::helper('shopbybrand')->deleteThumbnailImageFile($data['name'], $data['old_thumbnail_image']);
                unset($data['old_thumbnail_image']);
            }
            $data['thumbnail_image'] = "";

            if (isset($_FILES['thumbnail_image']))
                $data['thumbnail_image'] = Mage::helper('shopbybrand')->refineImageName($_FILES['thumbnail_image']['name']);


            if (!$data['thumbnail_image'] && isset($data['old_thumbnail_image'])) {
                $data['thumbnail_image'] = $data['old_thumbnail_image'];
            }

            $model = Mage::getModel('shopbybrand/brand');
            $model->load($this->getRequest()->getParam('id'))
                ->addData($data);
            try {
                if ($model->getCreatedTime() == NULL || $model->getUpdatedTime() == NULL) {
                    $model->setCreatedTime(now())
                            ->setUpdatedTime(now());
                } else {
                    $model->setUpdatedTime(now());
                }
                $productIds = array();
                if (isset($data['sproducts']) && $data['sproducts']) {
                    if (is_string($data['sproducts'])) {
                        parse_str($data['sproducts'], $productIds);
                        $productIds = array_unique(array_keys($productIds));
                    }
                    $model->setData('product_ids', implode(',', $productIds));
                }
                $model->setStoreId($store);
                try {
                    $model->save();
                    $categoryIds = Mage::helper('shopbybrand/brand')->getCategoryIdsByBrand($model);
                    if ($categoryIds != $model->getCategoryIds()) {
                        $model->setCategoryIds($categoryIds)
                                ->save();
                    }
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }

                //upload image
                $image = $model->getImage();
                if (isset($_FILES['image'])) {
                    if (isset($_FILES['image']['name']) && $_FILES['image']['name'])
                        $image = Mage::helper('shopbybrand')->uploadBrandImage($model->getId(), $_FILES['image']);
                }
                $thumbnailImage = $model->getThumbnailImage();
                if (isset($_FILES['thumbnail_image'])) {
                    if (isset($_FILES['thumbnail_image']['name']) && $_FILES['thumbnail_image']['name'])
                        $thumbnailImage = Mage::helper('shopbybrand')->uploadThumbnailImage($model->getId(), $_FILES['thumbnail_image']);
                }
                if ($image != $model->getImage() || $thumbnailImage != $model->getThumbnailImage()) {
                    if ($image != $model->getImage())
                        $model->setImage($image);
                    if ($thumbnailImage != $model->getThumbnailImage())
                        $model->setThumbnailImage($thumbnailImage);
                    $model->save();
                }
                
                $brandModel = Mage::getModel('shopbybrand/brand')
                        ->setStoreId($store)
                        ->load($model->getId());
                $brandModel->updateUrlKey();
                if(count($productIds))
                    Mage::helper('shopbybrand')->updateProductsBrand($productIds, $model->getId(), $store);
                
                $optionId = Mage::getResourceModel('shopbybrand/brand')->addOption($model->load($model->getId()));
                if($optionId)
                    $brandModel->setOptionId($optionId)->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('shopbybrand')->__('Brand was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId(), 'store' => $store));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('shopbybrand')->__('Unable to find brand to save')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete item action
     */
    public function deleteAction() {
        
        //$store = $this->getRequest()->getParam('store', 0);
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $stores = Mage::getModel('core/store')->getCollection()
                    ->addFieldToFilter('is_active',1)
                    ->addFieldToFilter('store_id',array('neq'=>0))
                    ;
                foreach ($stores as $store){
                    $urlRewrite = Mage::getModel('shopbybrand/urlrewrite')->loadByIdPath('brand/'.$this->getRequest()->getParam('id'), $store->getId());
                    if($urlRewrite->getId())
                        $urlRewrite->delete();
                }
                $model = Mage::getModel('shopbybrand/brand');
                $model->load($this->getRequest()->getParam('id'));
                Mage::getResourceModel('shopbybrand/brand')->removeOption($model);
                $model->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Brand was successfully deleted')
                );
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * mass delete item(s) action
     */
    public function massDeleteAction() {
        
        $shopbybrandIds = $this->getRequest()->getParam('shopbybrand');
        if (!is_array($shopbybrandIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select brand(s)'));
        } else {
            try {
                $stores = Mage::getModel('core/store')->getCollection()
                        ->addFieldToFilter('is_active',1)
                        ->addFieldToFilter('store_id',array('neq'=>0))
                        ;
                foreach ($shopbybrandIds as $shopbybrandId) {
                    foreach ($stores as $store){
                        $urlRewrite = Mage::getModel('shopbybrand/urlrewrite')->loadByIdPath('brand/'.$shopbybrandId, $store->getId());
                        if($urlRewrite->getId())
                            $urlRewrite->delete();
                    }
                    $shopbybrand = Mage::getModel('shopbybrand/brand')->load($shopbybrandId);
                    Mage::getResourceModel('shopbybrand/brand')->removeOption($shopbybrand);
                    $shopbybrand->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('adminhtml')->__('Total of %d record(s) were successfully deleted', count($shopbybrandIds))
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass change status for item(s) action
     */
    public function massStatusAction() {
        
        $shopbybrandIds = $this->getRequest()->getParam('shopbybrand');
        if (!is_array($shopbybrandIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($shopbybrandIds as $shopbybrandId) {
                    Mage::getSingleton('shopbybrand/brand')
                            ->load($shopbybrandId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) were successfully updated', count($shopbybrandIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
    /**
     * mass change is featured for item(s) action
     */
    public function massFeaturedAction() {
       
        $shopbybrandIds = $this->getRequest()->getParam('shopbybrand');
        if (!is_array($shopbybrandIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select brand(s)'));
        } else {
            try {
                foreach ($shopbybrandIds as $shopbybrandId) {
                    Mage::getSingleton('shopbybrand/brand')
                            ->load($shopbybrandId)
                            ->setIsFeatured($this->getRequest()->getParam('is_featured'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                        $this->__('Total of %d brand(s) were successfully updated', count($shopbybrandIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * export grid item to CSV type
     */
    public function exportCsvAction() {
        
        $fileName = 'shopbybrand.csv';
        $content = $this->getLayout()
                ->createBlock('shopbybrand/adminhtml_brand_grid')
                ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /*
     * export subcribers
     */

    public function exportCsvSubcribersAction() {
        
        $fileName = 'shopbybrand.csv';
        $content = $this->getLayout()
                ->createBlock('shopbybrand/adminhtml_brand_edit_tab_subcribers')
                ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportXmlSubcribersAction() {
        
        $fileName = 'shopbybrand.xml';
        $content = $this->getLayout()
                ->createBlock('shopbybrand/adminhtml_brand_edit_tab_subcribers')
                ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export grid item to XML type
     */
    public function exportXmlAction() {
       
        $fileName = 'shopbybrand.xml';
        $content = $this->getLayout()
                ->createBlock('shopbybrand/adminhtml_brand_grid')
                ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('shopbybrand');
    }

    public function testAction() {
        Mage::helper('shopbybrand/brand')->updateBrandsFormCatalog();
    }

    public function productAction() {
        
        $this->loadLayout();
        $this->getLayout()->getBlock('shopbybrand.block.adminhtml.brand.edit.tab.products')
                ->setBrandProducts($this->getRequest()->getPost('brand_products', null));
        $this->renderLayout();
    }

    public function productGridAction() {
       
        $this->loadLayout();
        $this->getLayout()->getBlock('shopbybrand.block.adminhtml.brand.edit.tab.products')
                ->setBrandProducts($this->getRequest()->getPost('brand_products', null));
        $this->renderLayout();
    }

    public function subcriberAction() {
        
        $this->loadLayout();
        $this->renderLayout();
    }

    public function orderItemsGridAction() {
        
        $this->getResponse()->setBody(
                $this->getLayout()->createBlock('shopbybrand/adminhtml_brand_edit_tab_orderitems')->toHtml()
        );
    }

    public function ajaxBlockAction() {
        
        $output = '';
        $blockTab = $this->getRequest()->getParam('block');
        if (in_array($blockTab, array(
                    'adminhtml_brand_edit_tab_report_graph',
                ))) {
            $output = $this->getLayout()->createBlock("shopbybrand/$blockTab")->toHtml();
        }
        $this->getResponse()->setBody($output);
    }
    
    protected function _licenseKeyError() {
        return false;
        
    }
}
