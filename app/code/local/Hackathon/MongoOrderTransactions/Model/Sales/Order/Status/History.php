<?php

class Hackathon_MongoOrderTransactions_Model_Sales_Order_Status_History extends Mage_Sales_Model_Order_Status_History
{
    public function save()
    {
        Mage::log(__METHOD__);
        if (!$this->getId())
        {
            Mage::log("Fresh Status History Save");
            $this->_getMongo()->saveOrderStatusHistory($this);
        }
        else
        {
            return parent::save();
        }
    }

    public function load($id, $field = null)
    {
        parent::load($id, $field);
        if (! $this->getId())
        {
            Mage::log(__METHOD__ . "... ( NOT FOUND IN MySQL)");
            if (is_null($field))
            {
                $field = $this->getResource()->getIdFieldName();
            }
            $mongoOrder = $this->_getMongo()->loadOrderStatusHistory($id, $field);
            if ($mongoOrder->getId())
            {
                $this->setData($mongoOrder->getOrder());
            }
        }
        return $this;
    }

    /**
     * @return Hackathon_MongoOrderTransactions_Model_Mongo
     */
    protected function _getMongo()
    {
        return Mage::getSingleton('hackathon_ordertransaction/mongo');
    }
}
