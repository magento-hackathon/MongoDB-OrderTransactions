<?php

class Hackathon_MongoOrderTransactions_Model_Sales_Order_Item extends Mage_Sales_Model_Order_Item
{
    public function save()
    {
        if (!$this->getId())
        {
            $this->_getMongo()->saveOrderItem($this);
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
            $mongoOrderItem = $this->_getMongo()->loadOrderItem($id, $field);
            if ($mongoOrderItem->getId())
            {
                $this->setData($mongoOrderItem->getOrder());
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
