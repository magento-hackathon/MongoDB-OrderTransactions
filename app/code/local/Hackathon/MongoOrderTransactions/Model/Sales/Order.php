<?php

class Hackathon_MongoOrderTransactions_Model_Sales_Order extends Mage_Sales_Model_Order
{
    public function save()
    {
        if (!$this->getId())
        {
            $this->_getMongo()->saveOrder($this;
        }
        else
        {
            return parent::save();
        }

    }

    /**
     * @return Hackathon_MongoOrderTransactions_Model_Mongo
     */
    protected function _getMongo()
    {
        return Mage::getSingleton('hackathon_ordertransaction/mongo');
    }
}