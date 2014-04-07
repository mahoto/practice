<?php

class Magestore_Shopbybrand_Block_Adminhtml_Brand_Edit_Tab_Subcribers extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('subcribers_grid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareCollection()
    {
        $brand = $this->getBrand();
        $subscriberIds = $brand->getSubscriberIds();
        $collection = Mage::getModel('newsletter/subscriber')
                ->getCollection()
                ->addFieldToFilter('subscriber_id', array('in'=>$subscriberIds))
                ->showCustomerInfo(true)
                ->addSubscriberTypeField()
                ->showStoreInfo()
                ;
        $storeId = $this->getStoreId();
        if($storeId)
            $collection->addFieldToFilter('store_id', $storeId);
        $this->setCollection($collection);
        return parent::_prepareCollection();
	}
    
    public function getStoreId(){
        $storeId = $this->getRequest()->getParam('store');
        return $storeId;
    }
	
	protected function _prepareColumns()
    {

        $this->addColumn('subscriber_id', array(
            'header'    => Mage::helper('shopbybrand')->__('ID'),
            'index'     => 'subscriber_id'
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
        
        $this->addColumn('subscriber_status', array(
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
		

        $this->addExportType('*/*/exportCsvSubcribers', Mage::helper('shopbybrand')->__('CSV'));
        $this->addExportType('*/*/exportXmlSubcribers', Mage::helper('shopbybrand')->__('Excel XML'));
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
    
    public function getBrand(){
        $branId = $this->getRequest()->getParam('id');
        return Mage::getModel('shopbybrand/brand')->load($branId);
    }

}
