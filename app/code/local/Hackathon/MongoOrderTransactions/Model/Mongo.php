<?php

class Hackathon_MongoOrderTransactions_Model_Mongo extends Varien_Object
{
    private $_mogodb = false;

    private $_tblSales = false;



    protected function _construct() {
        // automatische Verbindung mit localhost:27017
        $mongo = new Mongo();

        // $blog ist ein MongoDB-Objekt (vergleichbar mit MySQL-Datenbank, wird automatisch angelegt)

        $this->_mogodb = $mongo->magentoorder;
        // $posts ist eine MongoCollection (vergleichbar mit SQL-Tabelle, wird automatisch angelegt)

        $this->_tblSales = $this->_mogodb->sales;

    }

    private function setState($state) {

        $this->setState($state);

        return $this;
    }

    public function addItem($productId,$qty) {
        $items = $this->getItems();
        $items[$productId] = $qty;
        $this->setItems($items);

        return $this;
    }

    public function removeItem($productId) {

    }

    public function setQuoteId($quoteId) {
        $this->setQuoteId($quoteId);

        return $this;
    }

   
    public function insertQuote() {
        $this->setState('quote');

        $data = array(
            'state' => $this->getState(),
            'items' => $this->getItems(),
            'quote_id' => $this->getQutoeId(),
        );



        $this->_tblSales->insert($data);

    }

    public function getQuotes() {
        $quote = $this->_tblSales->findOne(array('quote_id' => 991));
        var_dump($quote);
    }

    public function loadQuote($quoteId) {
        $quote = $this->_tblSales->findOne($quoteId);
        $this->setData($quote);

        return $this;
    }

    public function saveQuote() {

    }

    public function saveOrder(Mage_Sales_Model_Order $order)
    {
        $quoteId = $order->getQuoteId();
        $this->loadQuote($quoteId);
        if (! $this->getData('_id'))
        {
            Mage::throwException(
                Mage::helper('hackathon_ordertransactions')->__('No associated quote with ID %s found in mongoDb', $quoteId)
            );
        }
        $quote->_tblSales->update(array('quote_id' => $quoteId), array('$set' => array(
            'order' => $order->getData(),
            'state' => 'order'
        )));
        return $this;
    }

}
