<?php

class Hackathon_MongoOrderTransactions_Model_Resource_Order_Collection extends Cm_Mongo_Model_Resource_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('hackathon_ordertransaction/order');
    }
}
