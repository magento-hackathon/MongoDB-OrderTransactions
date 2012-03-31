<?php

class Hackathon_MongoOrderTransactions_Model_Sales_Order extends Mage_Sales_Model_Order
{
    public function save()
    {
        if (!$this->getId())
        {
            $this->_getMongo()->saveOrder($this);
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
            if (is_null($field))
            {
                $field = $this->getResource()->getIdFieldName();
            }
            $mongoOrder = $this->_getMongo()->loadOrder($id, $field);
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