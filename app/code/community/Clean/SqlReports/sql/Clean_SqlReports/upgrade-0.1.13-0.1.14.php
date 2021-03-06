<?php

/** @var $this Mage_Core_Model_Resource_Setup */
$this->startSetup();

$this->getConnection()
    ->addColumn($this->getTable('cleansql/report'), 'filter_config', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'length'    => '65536',
        'comment'   => 'Filter Configuration',
    ));

$this->endSetup();
