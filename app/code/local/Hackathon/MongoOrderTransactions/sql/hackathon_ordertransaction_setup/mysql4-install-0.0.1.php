<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('hackathon_ordertransaction/order_seq');
if ($installer->tableExists($tableName))
{
    $installer->getConnection()->dropTable($tableName);
}
$table = $installer->getConnection()->newTable($tableName);
$table->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
    'primary' => true,
    'unsigned' => true,
    'identity' => true,
), 'Order ID Sequence Table');
$installer->getConnection()->createTable($table);


/* @var $orderResource Mage_Sales_Model_Resource_Order */
$orderResource = Mage::getResourceModel('sales/order');

$sql = "SELECT MAX({$orderResource->getIdFieldName()}) FROM {$orderResource->getMainTable()}";
$lastId = $installer->getConnection()->fetchOne($sql);
if (! $lastId)
{
    $lastId = 1;
}
$installer->run("ALTER TABLE {$tableName} AUTO_INCREMENT = {$lastId}");

$installer->endSetup();