<?php

class Ophirah_Qquoteadv_Model_Mysql4_Quoteshippingrate
    extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the id refers to the key field in your database table.
        $this->_init('qquoteadv/quoteshippingrate', 'rate_id');
    }
}