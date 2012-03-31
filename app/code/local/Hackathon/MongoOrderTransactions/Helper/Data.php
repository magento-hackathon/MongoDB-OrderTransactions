<?php

class Hackathon_MongoOrderTransactions_Helper_Data extends Mage_Core_Model_Abstract
{
    public function getMongoDbName()
    {
        return (string) Mage::getConfig()->getNode('global/hackathon_ordertransaction/mongo_db');
    }
}
