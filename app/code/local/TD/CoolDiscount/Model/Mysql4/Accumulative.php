<?php
/**
 * Created by PhpStorm.
 * User: svatoslavzilicev
 * Date: 05.04.14
 * Time: 15:14
 */
class TD_CoolDiscount_Model_Mysql4_Accumulative extends Mage_Core_Model_Mysql4_Abstract{
    public function _construct()
    {
        // Note that the bannernext_id refers to the key field in your database table.
        $this->_init('td_cooldiscount/accumulative','id');
    }
}