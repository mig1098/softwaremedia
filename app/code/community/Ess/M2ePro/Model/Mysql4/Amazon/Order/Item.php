<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Amazon_Order_Item extends Ess_M2ePro_Model_Mysql4_Abstract
{
    protected $_isPkAutoIncrement = false;

    public function _construct()
    {
        $this->_init('M2ePro/Amazon_Order_Item', 'order_item_id');
        $this->_isPkAutoIncrement = false;
    }
}