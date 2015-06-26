<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Reports
 *
 * @author david
 */
class SoftwareMedia_Swmreports_Block_Adminhtml_Licensing extends Mage_Adminhtml_Block_Widget_Grid_Container {

	public function __construct() {
		$this->_controller = 'adminhtml_licensing';
		$this->_blockGroup = 'licensing';
		$this->_headerText = Mage::helper('outofstock')->__('Download Report');
		parent::__construct();
		$this->_removeButton('add');
	}

}
