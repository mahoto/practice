<?php

class Magestore_Shopbybrand_Block_Adminhtml_Brand_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Grid
{
    
     public function __construct()
    {
        parent::__construct();
        $this->setId('productsgrid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
        if ($this->getBrand()->getId()) {
            $this->setDefaultFilter(array('in_products'=>1));
        }
    }

    protected function _addColumnFilterToCollection($column)
    {
        // Set custom filter for in product flag
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', array('in' => $productIds));
            } else {
                if($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', array('nin' => $productIds));
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }
    
    public function getProductTypeIds(){
        $attributeCode = Mage::helper('shopbybrand/brand')->getAttributeCode();
        $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', $attributeCode);
        $applyTo = $attributeModel->getData('apply_to');
        if($applyTo)
            $types = explode(',', $applyTo);
        else
            $types = array();
        return $types;
    }

    /**
     * Prepare collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
     protected function _prepareCollection(){
        $collection = Mage::getModel('catalog/product')
            ->getCollection()
            ->addAttributeToSelect('*')
            //->addAttributeToFilter('manufacturer', array('notnull'=>true))
        ;
        $types = $this->getProductTypeIds();
        if(count($types)){
            $collection->addFieldToFilter('type_id', array('in'=>$types));
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
	
        
    }


    /**
     * Add columns to grid
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
       $this->addColumn('in_products', array(
			'header_css_class'  => 'a-center',
			'type'              => 'checkbox',
			'name'              => 'in_products',
			'values'            => $this->_getSelectedProducts(),
			'align'             => 'center',	
			'index'             => 'entity_id'
		));
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('shopbybrand')->__('ID'),
            'sortable'  => true,
            'width'     => 60,
            'index'     => 'entity_id'
        ));

        $this->addColumn('product_name', array(
            'header'    => Mage::helper('shopbybrand')->__('Name'),
            'index'     => 'name'
        ));
        $productTypes = $this->getProductTypeIds();
        $types = Mage::getSingleton('catalog/product_type')->getOptionArray();
        $newTypes = array();
        foreach($productTypes as $type){
            if(key_exists($type, $types)){
                $newTypes[$type] = $types[$type];
            }
        }
        $this->addColumn('type', array(
            'header'    => Mage::helper('shopbybrand')->__('Type'),
            'width'     => 100,
            'index'     => 'type_id',
            'type'      => 'options',
            'options'   => $newTypes,
        ));
        
        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name', array(
            'header'    => Mage::helper('shopbybrand')->__('Attrib. Set Name'),
            'width'     => 130,
            'index'     => 'attribute_set_id',
            'type'      => 'options',
            'options'   => $sets,
        ));

        $this->addColumn('product_status', array(
            'header'    => Mage::helper('shopbybrand')->__('Status'),
            'width'     => 90,
            'index'     => 'status',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_status')->getOptionArray(),
        ));

        $this->addColumn('visibility', array(
            'header'    => Mage::helper('shopbybrand')->__('Visibility'),
            'width'     => 90,
            'index'     => 'visibility',
            'type'      => 'options',
            'options'   => Mage::getSingleton('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('shopbybrand')->__('SKU'),
            'width'     => 80,
            'index'     => 'sku'
        ));

        $this->addColumn('price', array(
            'header'        => Mage::helper('shopbybrand')->__('Price'),
            'type'          => 'currency',
            'currency_code' => (string) Mage::getStoreConfig(Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE),
            'index'         => 'price'
        ));
        $this->addColumn('position', array(
            'header'            => Mage::helper('shopbybrand')->__('Position'),
            'name'              => 'position',    
            'index'             => 'position',
            'width'             => 0,
            'editable'          => true,
            'filter'   => false,
       ));

        return parent::_prepareColumns();
    }
    
    /**
     * Rerieve grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getData('grid_url')
            ? $this->getData('grid_url')
            : $this->getUrl('*/*/productGrid', array('_current' => true,'id'=>$this->getRequest()->getParam('id')));
    }

    /**
     * Retrieve selected related products
     *
     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getBrandProducts();
        if (!is_array($products)) {
            $products = array_keys($this->getSelectedProducts());
        }
        return $products;
    }

    /**
     * Retrieve related products
     *
     * @return array
     */
    public function getSelectedProducts()
    {
        $products = array();
        $attributeCode = 'manufacturer';
        $brand = $this->getBrand();
        $productIds = explode(',', $brand->getData('product_ids'));
        foreach ($productIds as $productId){
            $products[$productId] = array('position' => 0);
        }
        return $products;
    }
    
    public function getBrand(){
        $brandId=$this->getRequest()->getParam('id');
        $brand = Mage::getModel('shopbybrand/brand')->load($brandId);
        return $brand;
    }
}
