<?php

class Hackathon_MongoOrderTransactions_Model_Sales_Order_Payment extends Mage_Sales_Model_Order_Payment
{
    public function save()
    {
        Mage::log(__METHOD__);
        if (!$this->getId())
        {
            Mage::log("Fresh Payment Save");
            $this->_getMongo()->saveOrderPayment($this);
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
            $mongoOrder = $this->_getMongo()->loadOrderPayment($id, $field);
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
