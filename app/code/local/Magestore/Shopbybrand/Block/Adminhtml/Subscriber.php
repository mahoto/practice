<?php

class Magestore_Shopbybrand_Block_Adminhtml_Subscriber extends Mage_Adminhtml_Block_Widget_Grid_Container
{
   public function __construct()
  {
    $this->_controller = 'adminhtml_subscriber';
    $this->_blockGroup = 'shopbybrand';
    $this->_headerText = Mage::helper('shopbybrand')->__('Subscriber Manage');
    $this->_addButtonLabel = Mage::helper('shopbybrand')->__('Add Item');
    parent::__construct();
	 $this->_removeButton('add');
  }
}