<?php
/**
 * Created by PhpStorm.
 * User: svatoslavzilicev
 * Date: 05.04.14
 * Time: 0:18
 */
class TD_CoolDiscount_Block_Adminhtml_Discount extends Mage_Adminhtml_Block_Template
 implements Mage_Adminhtml_Block_Widget_Tab_Interface{

    public function getTabLabel()
    {
        return Mage::helper('customer')->__('Discount');
    }

    public function getTabTitle()
    {
        return Mage::helper('customer')->__('Discount');
    }

    public function canShowTab()
    {
        if (Mage::registry('current_customer')->getId()) {
            return true;
        }
        return false;
    }

    public function isHidden()
    {
        if (Mage::registry('current_customer')->getId()) {
            return false;
        }
        return true;
    }
}