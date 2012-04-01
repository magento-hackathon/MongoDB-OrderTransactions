<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$tableName = $installer->getTable('hackathon_ordertransaction/order_item_seq');
if ($installer->tableExists($tableName))
{
    $installer->getConnection()->dropTable($tableName);
}
$table = $installer->getConnection()->newTable($tableName);
$table->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
    'primary' => true,
    'unsigned' => true,
    'identity' => true,
), 'Sequence Column')
    ->addColumn('handle', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array(
    'unsigned' => true,
    'null' => false,
), 'Key Column')
->addIndex(
    $installer->getConnection()->getIndexName($tableName, array('handle')),
    array('handle'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
)
->setComment('Order Item ID Sequence Table');
$installer->getConnection()->createTable($table);


/* @var $resource Mage_Sales_Model_Resource_Order_Item */
$resource = Mage::getResourceModel('sales/order_item');

$sql = "SELECT MAX({$resource->getIdFieldName()}) FROM {$resource->getMainTable()}";
$lastId = $installer->getConnection()->fetchOne($sql);
if (! $lastId)
{
    $lastId = 1;
}
$installer->run("
ALTER TABLE {$tableName} AUTO_INCREMENT = {$lastId};
INSERT INTO {$tableName} (id, handle) VALUES ($lastId, 1);
");




$tableName = $installer->getTable('hackathon_ordertransaction/order_address_seq');
if ($installer->tableExists($tableName))
{
    $installer->getConnection()->dropTable($tableName);
}
$table = $installer->getConnection()->newTable($tableName);
$table->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 10, array(
    'primary' => true,
    'unsigned' => true,
    'identity' => true,
), 'Sequence Column')
    ->addColumn('handle', Varien_Db_Ddl_Table::TYPE_INTEGER, 1, array(
    'unsigned' => true,
    'null' => false,
), 'Key Column')
->addIndex(
    $installer->getConnection()->getIndexName($tableName, array('handle')),
    array('handle'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
)
->setComment('Order Address ID Sequence Table');
$installer->getConnection()->createTable($table);


/* @var $resource Mage_Sales_Model_Resource_Order_address */
$resource = Mage::getResourceModel('sales/order_address');

$sql = "SELECT MAX({$resource->getIdFieldName()}) FROM {$resource->getMainTable()}";
$lastId = $installer->getConnection()->fetchOne($sql);
if (! $lastId)
{
    $lastId = 1;
}
$installer->run("
ALTER TABLE {$tableName} AUTO_INCREMENT = {$lastId};
INSERT INTO {$tableName} (id, handle) VALUES ($lastId, 1);
");


$installer->endSetup();