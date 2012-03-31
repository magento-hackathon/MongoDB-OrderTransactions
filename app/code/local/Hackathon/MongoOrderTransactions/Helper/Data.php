<?php

class Hackathon_MongoOrderTransactions_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getMongoDbName()
    {
        return (string) Mage::getConfig()->getNode('global/hackathon_ordertransaction/mongo_db');
    }

    public function getNewOrderIdFromSequence()
    {
        /* @var $connection Varien_Db_Adapter_Pdo_Mysql */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('default_read');
        $table = $resource->getTableName('hackathon_ordertransaction/order_seq');
        $connection->query("REPLACE INTO {$table} SET handle=1");
        return $connection->lastInsertId($table);
    }
}
