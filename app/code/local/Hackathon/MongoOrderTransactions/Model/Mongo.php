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

        $this->setData('state',$state);

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
        $this->setData('quote_id',$quoteId);

        return $this;
    }

   
    public function insertQuote() {
        $this->setState('quote');

        $data = array(
            'state' => $this->getState(),
            'items' => $this->getItems(),
            'quote_id' => $this->getQuoteId(),
        );



        $this->_tblSales->insert($data);

    }

    public function getId() {
        if(!is_object($this->getData('_id')))
            return false;
        return $this->getData('_id')->__toString();
    }

    public function getQuotes() {
        $quotes = $this->_tblSales->find();
        return $quotes;
    }

    public function loadQuote($quoteId) {
        $this->setData(array());
        $quote = $this->_tblSales->findOne(array('quote_id' => $quoteId));
        $this->setData($quote);

        return $this;
    }

    public function deleteQuote($quoteId) {
        $this->loadQuote($quoteId);
            if($this->getId() !== false) {
                $this->setState('deleted')
                    ->updateQuote();
            }
        return $this;
    }

    public function updateQuote() {
        if($this->getId() !== false) {
            $data = array(
            'state' => $this->getState(),
            'items' => $this->getItems(),
            'quote_id' => $this->getQuoteId(),
            );
            $this->_tblSales->update(array('_id' => new MongoId($this->getId())),array('$set' => $data));
        } else {
            throw new Exception('could not update quote (no id set)');
        }
        return $this;
    }

    public function truncate() {
        $this->_tblSales->remove(array(), array("save" => true));
        return $this;
    }

}
