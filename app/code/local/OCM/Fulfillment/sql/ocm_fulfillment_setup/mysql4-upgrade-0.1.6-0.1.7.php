<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->addAttribute('catalog_product', 'cost_override', array(
        'backend'       => '',
        'frontend'      => '',
        'class' => '',
        'default'       => '',
        'label' => 'Cost Override',
        'input' => 'price',
        'type'  => 'decimal',
        'source'        => '',
        'global'        => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
        'visible'       => 1,
        'required'      => 0,
        'searchable'    => 0,
        'filterable'    => 1,
        'unique'        => 0,
        'comparable'    => 0,
        'visible_on_front' => 0,
        'is_html_allowed_on_front' => 0,
        'user_defined'  => 1,
));


$installer->endSetup();