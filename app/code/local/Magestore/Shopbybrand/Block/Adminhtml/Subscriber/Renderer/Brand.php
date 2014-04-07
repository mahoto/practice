<?php

class Magestore_Shopbybrand_Block_Adminhtml_Subscriber_Renderer_Brand extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract{
	
	public function render(Varien_Object $row){
		$subscriber_id = $row->getSubscriberId();
        $brand_ids=Mage::getModel('shopbybrand/brandsubscriber')
                ->getCollection()
                ->addFieldToFilter('subscriber_id',$subscriber_id)->getAllBrandId();
		$brand = NULL;
        $brands=Mage::getModel('shopbybrand/brand')->getCollection()->addFieldToFilter('brand_id',array($brand_ids));
        foreach ($brands as $value) {
            $brand=$brand.$value->getName().',';
        }
		return $brand;
	}
    
}