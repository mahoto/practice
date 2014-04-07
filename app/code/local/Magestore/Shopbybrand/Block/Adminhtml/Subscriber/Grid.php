<?php

class Magestore_Shopbybrand_Block_Adminhtml_Subscriber_Grid extends Mage_Adminhtml_Block_Widget_Grid{
//viet lai ham ket noi trong grid cua newsletter
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('shopbybrand/subscriber')
                ->getCollection()
                ->showCustomerInfo(true)
                ->addSubscriberTypeField()
                ->showStoreInfo();
        $resource = Mage::getSingleton('core/resource');
         $collection->getSelect()
                ->join(array('brandsubscriber'=>$resource->getTableName('brand_subscriber')),'main_table.subscriber_id=brandsubscriber.subscriber_id' )
                 ->join(array('brand'=>$resource->getTableName('brand')),'brandsubscriber.brand_id=brand.brand_id',array('bands'  => new Zend_Db_Expr('group_concat(brand.name SEPARATOR ",")')) )
                 ->group('main_table.subscriber_id');
         
        $this->setCollection($collection);
        return parent::_prepareCollection();
	}
	protected function _addColumnFilterToCollection($column) {
        if ($this->getCollection()) {
            $field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
            if ($column->getFilterConditionCallback()) {
                call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
            } else {
                $cond = $column->getFilter()->getCondition();
                if ($field && isset($cond)) {
                    $this->getCollection()->addFieldToHavingFilter($field, $cond);
                }
            }
        }
        return $this;
    }
	protected function _prepareColumns()
    {

        $this->addColumn('subscriber_id', array(
            'header'    => Mage::helper('shopbybrand')->__('ID'),
            'index'     => 'subscriber_id',
            'filter_index'=>'main_table.subscriber_id'
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('shopbybrand')->__('Email'),
            'index'     => 'subscriber_email'
        ));

        $this->addColumn('type', array(
            'header'    => Mage::helper('shopbybrand')->__('Type'),
            'index'     => 'type',
            'type'      => 'options',
            'options'   => array(
                1  => Mage::helper('shopbybrand')->__('Guest'),
                2  => Mage::helper('shopbybrand')->__('Customer')
            )
        ));
        
        $this->addColumn('bands', array(
            'header'    => Mage::helper('shopbybrand')->__('Brand'),
            'index'     => 'bands',
            'filter_index'=> new Zend_Db_Expr('group_concat(brand.name SEPARATOR ",")')
        ));
        
        $this->addColumn('firstname', array(
            'header'    => Mage::helper('shopbybrand')->__('Customer First Name'),
            'index'     => 'customer_firstname',
            'default'   =>    '----'
        ));

        $this->addColumn('lastname', array(
            'header'    => Mage::helper('shopbybrand')->__('Customer Last Name'),
            'index'     => 'customer_lastname',
            'default'   =>    '----'
        ));

        $options=array(
                Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE   => Mage::helper('shopbybrand')->__('Not Activated'),
                Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED   => Mage::helper('shopbybrand')->__('Subscribed'),
                Mage_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED => Mage::helper('shopbybrand')->__('Unsubscribed'),
            );
        
        if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
            $options[Mage_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED]= Mage::helper('shopbybrand')->__('Unconfirmed');
        }
        
        $this->addColumn('status', array(
            'header'    => Mage::helper('shopbybrand')->__('Status'),
            'index'     => 'subscriber_status',
            'type'      => 'options',
            'options'   => $options
        ));

        $this->addColumn('website', array(
            'header'    => Mage::helper('shopbybrand')->__('Website'),
            'index'     => 'website_id',
            'type'      => 'options',
            'options'   => $this->_getWebsiteOptions()
        ));

        $this->addColumn('group', array(
            'header'    => Mage::helper('shopbybrand')->__('Store'),
            'index'     => 'group_id',
            'type'      => 'options',
            'options'   => $this->_getStoreGroupOptions()
        ));

        $this->addColumn('store', array(
            'header'    => Mage::helper('shopbybrand')->__('Store View'),
            'index'     => 'store_id',
            'type'      => 'options',
            'options'   => $this->_getStoreOptions()
        ));
		

        $this->addExportType('*/*/exportCsv', Mage::helper('shopbybrand')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('shopbybrand')->__('Excel XML'));
        return parent::_prepareColumns();
    }
	  /**
     * Convert OptionsValue array to Options array
     *
     * @param array $optionsArray
     * @return array
     */
    protected function _getOptions($optionsArray)
    {
        $options = array();
        foreach ($optionsArray as $option) {
            $options[$option['value']] = $option['label'];
        }
        return $options;
    }

    /**
     * Retrieve Website Options array
     *
     * @return array
     */
    protected function _getWebsiteOptions()
    {
        return Mage::getModel('adminhtml/system_store')->getWebsiteOptionHash();
    }

    /**
     * Retrieve Store Group Options array
     *
     * @return array
     */
    protected function _getStoreGroupOptions()
    {
        return Mage::getModel('adminhtml/system_store')->getStoreGroupOptionHash();
    }

    /**
     * Retrieve Store Options array
     *
     * @return array
     */
    protected function _getStoreOptions()
    {
        return Mage::getModel('adminhtml/system_store')->getStoreOptionHash();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('subscriber_id');
        $this->getMassactionBlock()->setFormFieldName('subscriber');

        $this->getMassactionBlock()->addItem('unsubscribe', array(
             'label'        => Mage::helper('shopbybrand')->__('Unsubscribe'),
             'url'          => $this->getUrl('*/*/massUnsubscribe')
        ));

        $this->getMassactionBlock()->addItem('delete', array(
             'label'        => Mage::helper('shopbybrand')->__('Delete'),
             'url'          => $this->getUrl('*/*/massDelete')
        ));

        return $this;
    }
}