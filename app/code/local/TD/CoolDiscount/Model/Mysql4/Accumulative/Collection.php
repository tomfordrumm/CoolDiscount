<?php
/**
 * Created by PhpStorm.
 * User: svatoslavzilicev
 * Date: 05.04.14
 * Time: 15:15
 */
class TD_CoolDiscount_Model_Mysql4_Accumulative_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract{
    public function _construct()
    {
        parent::_construct();
        $this->_init('td_cooldiscount/accumulative');
    }
}