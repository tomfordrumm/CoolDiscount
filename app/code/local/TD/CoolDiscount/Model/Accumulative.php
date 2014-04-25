<?php
/**
 * Created by PhpStorm.
 * User: svatoslavzilicev
 * Date: 05.04.14
 * Time: 15:14
 */
class TD_CoolDiscount_Model_Accumulative extends Mage_Core_Model_Abstract{

    public function _construct()
    {
        parent::_construct();
        $this->_init('td_cooldiscount/accumulative');
    }

    public function loadByCustomerId($id){
        $collection = Mage::getModel('td_cooldiscount/accumulative')->getCollection()
            ->addFieldToFilter('customer_id',array('eq'=>$id));
        if (count($collection)>0){
            foreach ($collection as $item){
                return $item;
            }
        } else {
            return false;
        }
    }
}