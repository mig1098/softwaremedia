<?php

class OCM_Peachtree_Model_Mysql4_Referer extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('peachtree/ocm_peachtree_referer', 'id');
    }
}