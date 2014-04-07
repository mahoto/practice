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
 * Shopbybrand Index Controller
 * 
 * @category    Magestore
 * @package     Magestore_Shopbybrand
 * @author      Magestore Developer
 */
class Magestore_Shopbybrand_IndexController extends Mage_Core_Controller_Front_Action {

    protected function _initAction() {
        $store = Mage::app()->getStore()->getId();
        $enable = Mage::getStoreConfig('shopbybrand/general/enable', $store);
        if (!$enable)
            $this->_redirectUrl(Mage::getBaseUrl());
    }

    /**
     * index action
     */
    public function indexAction() {
       
        $this->_initAction();
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Shop by Brand'));
        $this->getLayout()->getBlock('breadcrumbs')
                ->addCrumb('home', array('label' => $this->__('Home'),
                    'title' => $this->__('Go to Home Page'),
                    'link' => Mage::getBaseUrl()))
                ->addCrumb('socialvoice', array('label' => $this->__('Brands'),
                    'title' => $this->__('Shop by Brand'),
                ))
        ;
        $this->renderLayout();
    }

    public function viewAction() {
        
        $this->_initAction();
        $this->loadLayout();
        
        $brandId = $this->getRequest()->getParam('id');
        $storeId = Mage::app()->getStore()->getId();
        $brand = Mage::getModel('shopbybrand/brand')->setStoreId($storeId)
                ->load($brandId);
        $head = $this->getLayout()->getBlock('head');
        $head->setTitle($brand->getName());
        $head->setKeywords($brand->getMetaKeywords());
        $head->setDescription($brand->getMetaDescription());
        $moduleUrl = Mage::getStoreConfig('shopbybrand/general/router', $storeId);
        $this->getLayout()->getBlock('breadcrumbs')
                ->addCrumb('home', array('label' => $this->__('Home'),
                    'title' => $this->__('Go to Home Page'),
                    'link' => Mage::getBaseUrl()
                ))
                ->addCrumb('brand', array('label' => $this->__('Brands'),
                    'title' => $this->__('Shop by Brand'),
                    'link' => Mage::getUrl($moduleUrl)
                ))
                ->addCrumb('view', array('label' => $brand->getName(),
                    'title' => $this->__('Shop by Brand')
                ))
        ;
        $this->renderLayout();
    }

    public function ajaxUpdateBrandAction() {
        
        $block = Mage::getBlockSingleton('shopbybrand/ajaxupdate');
        $this->getResponse()->setBody(json_encode($block->toHtml()));
    }

    public function subscribeAction() {
        
        $this->_initAction();
        $email = $this->getRequest()->getParam('email');
        $brandId = $this->getRequest()->getParam('brand_id');
        $session = Mage::getSingleton('core/session');
        /*
          $subscribe = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
          if ($subscribe->getId()) {
          $model=$this->getBrandsubscribeModel($brandId,$subscribe->getId());
          $model->setBrandId($brandId)
          ->setSubscriberId($subscribe->getId());
          try {
          $model->save();
          $session->addSuccess($this->__('Thank you for your subscription.'));
          } catch (Exception $e) {
          $session->addException($e, $this->__('There was a problem with the subscription.'));
          }
          } else */ {
            $this->subscribeBrand();
            $subscribe = Mage::getModel('newsletter/subscriber')->loadByEmail($email);
            if ($subscribe->getId()) {
                $model = $this->getBrandsubscribeModel($brandId, $subscribe->getId())
                        ->setBrandId($brandId)
                        ->setSubscriberId($subscribe->getId());
                try {
                    $model->save();
                } catch (Exception $e) {
                    $session->addException($e, $this->__('There was a problem with the subscription.'));
                }
            }
        }
        $refererUrl = $this->getRequest()->getServer('HTTP_REFERER');
        ;
        $this->_redirectUrl($refererUrl);
    }

    public function subscribeBrand() {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            if (version_compare(Mage::getVersion(), '1.4.1.1', '>=')) {
                $session = Mage::getSingleton('core/session');
                $customerSession = Mage::getSingleton('customer/session');
                $email = (string) $this->getRequest()->getPost('email');

                try {
                    if (!Zend_Validate::is($email, 'EmailAddress')) {
                        Mage::throwException($this->__('Please enter a valid email address.'));
                    }

                    if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 &&
                            !$customerSession->isLoggedIn()) {
                        Mage::throwException($this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl()));
                    }

                    $ownerId = Mage::getModel('customer/customer')
                            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                            ->loadByEmail($email)
                            ->getId();
                    if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                        Mage::throwException($this->__('This email address is already assigned to another user.'));
                    }

                    $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                    if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                        $session->addSuccess($this->__('Confirmation request has been sent.'));
                    } else {
                        $session->addSuccess($this->__('Thank you for your subscription.'));
                    }
                } catch (Mage_Core_Exception $e) {
                    $session->addException($e, $this->__('There was a problem with the subscription: %s', $e->getMessage()));
                } catch (Exception $e) {
                    $session->addException($e, $this->__('There was a problem with the subscription.'));
                }
            } else {
                $session = Mage::getSingleton('core/session');
                $email = (string) $this->getRequest()->getPost('email');

                try {
                    if (!Zend_Validate::is($email, 'EmailAddress')) {
                        Mage::throwException($this->__('Please enter a valid email address'));
                    }

                    $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                    if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                        $session->addSuccess($this->__('Confirmation request has been sent'));
                    } else {
                        $session->addSuccess($this->__('Thank you for your subscription'));
                    }
                } catch (Mage_Core_Exception $e) {
                    $session->addException($e, $this->__('There was a problem with the subscription: %s', $e->getMessage()));
                } catch (Exception $e) {
                    $session->addException($e, $this->__('There was a problem with the subscription'));
                }
            }
        }
    }

    public function getBrandsubscribeModel($brandId, $subscriberId) {
        $collection = Mage::getModel('shopbybrand/brandsubscriber')
                ->getCollection()
                ->addFieldToFilter('brand_id', $brandId)
                ->addFieldToFilter('subscriber_id', $subscriberId);
        if ($collection->getSize())
            return $collection->getFirstItem();
        return Mage::getModel('shopbybrand/brandsubscriber');
    }

    public function testAction() {
        Mage::helper('shopbybrand/brand')->getMostSubscriber();
    }
    
    protected function _licenseKeyError() {
        $this->getRequest()->initForward();
        $this->getRequest()->setActionName('noRoute')->setDispatched(false);
        return $this;
    }
}