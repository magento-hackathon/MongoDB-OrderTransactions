<?php

class Hackathon_MongoOrderTransactions_Model_Mongo extends Mage_Core_Model_Abstract
{
    private $_mogodb = false;

    private $_tblSales = false;

    protected function _construct()
    {
        $this->_init('hackathon_ordertransaction/mongo');
    }



    private function _connect() {
        // automatische Verbindung mit localhost:27017
        $mongo = new Mongo();

        // $blog ist ein MongoDB-Objekt (vergleichbar mit MySQL-Datenbank, wird automatisch angelegt)

        $this->_mogodb = $mongo->magentoorder;
        // $posts ist eine MongoCollection (vergleichbar mit SQL-Tabelle, wird automatisch angelegt)

        $this->_tblSales = $this->_mogodb->sales;

    }

    public function setState() {

    }

    public function addItem() {

    }

    public function setQuoteId() {

    }

    public function test() {
        
    }

    public function insertQuote($data) {
        $data = array(
            'state' => 'quote',
            'items' => array(
                1 => 'test1',
                2 => 'test2'
            ),
            'quote_id' => 991
        );

        $this->_tblSales->insert($data);

    }

    public function getQuotes() {
        $quote = $this->_tblSales->findOne(array('quote_id' => 991));
        var_dump($quote);
    }

}
