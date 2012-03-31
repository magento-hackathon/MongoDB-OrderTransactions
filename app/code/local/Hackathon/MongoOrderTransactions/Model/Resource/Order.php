<?php

class Hackathon_MongoOrderTransactions_Model_Resource_Order extends Cm_Mongo_Model_Resource_Abstract
{
    protected function _construct()
    {
        $this->_init('hackathon_ordertransaction/order');
    }
}
