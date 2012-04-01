<?php

class Hackathon_MongoOrderTransactions_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getMongoDbName()
    {
        return (string) Mage::getConfig()->getNode('global/hackathon_ordertransaction/mongo_db');
    }

    public function _getNewIdFromSequence($seqTable)
    {
        /* @var $connection Varien_Db_Adapter_Pdo_Mysql */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('default_read');
        $table = $resource->getTableName($seqTable);
        $connection->query("REPLACE INTO {$table} SET handle=1");
        return $connection->lastInsertId($table);
    }

    public function getNewOrderIdFromSequence()
    {
        return $this->_getNewIdFromSequence('hackathon_ordertransaction/order_seq');
    }

    public function getNewOrderItemIdFromSequence()
    {
        return $this->_getNewIdFromSequence('hackathon_ordertransaction/order_item_seq');
    }

    public function getNewOrderAddressIdFromSequence()
    {
        return $this->_getNewIdFromSequence('hackathon_ordertransaction/order_address_seq');
    }

    public function getNewOrderPaymentIdFromSequence()
    {
        return $this->_getNewIdFromSequence('hackathon_ordertransaction/order_payment_seq');
    }

    public function getNewOrderStatusHistoryIdFromSequence()
    {
        return $this->_getNewIdFromSequence('hackathon_ordertransaction/order_status_history_seq');
    }
}
