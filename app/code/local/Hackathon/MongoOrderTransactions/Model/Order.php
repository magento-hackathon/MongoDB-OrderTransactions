<?php

class Hackathon_MongoOrderTransactions_Model_Order extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('hackathon_ordertransaction/order');
    }
}
