<?php

class Hackathon_MongoOrderTransactions_Model_Sales_Order_Address extends Mage_Sales_Model_Order_Address
{
    public function save()
    {
        if (!$this->getId())
        {
            $this->_getMongo()->saveOrderAddress($this);
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
            $mongoOrderAddress = $this->_getMongo()->loadOrderAddress($id, $field);
            if ($mongoOrderAddress->getId())
            {
                $this->setData($mongoOrderAddress->getOrder());
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
