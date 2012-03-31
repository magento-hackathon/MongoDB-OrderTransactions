<?php

class Hackathon_MongoOrderTransactions_Model_Mongo extends Varien_Object
{
    /**
     * @var MongoDB
     */
    private $_mogodb = false;

    /**
     * @var MongoCollection
     */
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
        $this->setData('state', $state);

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
        if($this->getId() !== false) {
            $this->_tblSales->remove(array('quote_id' => $quoteId, 'justOne' => true));
        }
    }

    /**
	 * Saves the quote in MongoDB.
	 * 
	 * @todo: update insertQuote() to use saveQuote() or refactor code
	 *        to use saveQuote() as save() does automatically choose correctly
	 *        between update and insert. 
	 * 
	 * @return void
	 */
	public function saveQuote()
	{
		$this->setState('quote');
		
        $data = array(
        	'_id' => new MongoId($this->getId()),
            'state' => $this->getState(),
            'items' => $this->getItems(),
            'quote_id' => $this->getQuoteId(),
        );

        $this->_tblSales->save($data);
	}
	
    public function saveOrder(Mage_Sales_Model_Order $order)
    {
        $quoteId = $order->getQuoteId();
        $this->loadQuote($quoteId);
        if (! $this->getId())
        {
            Mage::throwException(
                Mage::helper('hackathon_ordertransaction')->__('No associated quote with ID %s found in mongoDb', $quoteId)
            );
        }
        $orderId = Mage::helper('hackathon_ordertransaction')->getNewOrderIdFromSequence();
        $order->setId($orderId);
        $this->_tblSales->update(array('quote_id' => $quoteId), array('$set' => array(
            'order' => $order->getData(),
            'state' => 'order'
        )));
        return $this;
    }

    public function loadOrder($id, $field)
    {
        $this->setData(array());
        $this->_tblSales->ensureIndex("order.$field");
        $result = $this->_tblSales->findOne(array("order.$field" => $id));
        if ($result['_id'])
        {
            $this->setData($result);
        }
        return $this;
    }

    /**
     * Set the state to be deleted when cleanup is run
     *
     * @return null
     **/
    public function setToDelete($quoteId)
    {
        $this->_tblSales->update(
            array('quote_id' => $quoteId), array(
                '$set' => array(
                    'state' => 'delete'
                )
            )
        );
    }

    /**
     * Set the state to be order
     *
     * To be used as a rollback function should the save
     * to persistent DB.
     *
     * @return null
     **/
    public function revertToOrder($quoteId)
    {
        $this->_tblSales->update(
            array('quote_id' => $quoteId), array(
                '$set' => array(
                    'state' => 'order'
                )
            )
        );
    }

    /**
     * Remove all quotes with a state of delete
     *
     * @return null
     **/
    public function clean()
    {
        $this->_tblSales->remove(array('state' => 'delete'));
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
