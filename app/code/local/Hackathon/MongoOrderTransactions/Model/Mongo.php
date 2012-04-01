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

    public function getId()
    {
        if(!is_object($this->getData('_id')))
        {
            return null;
        }
        return (string) $this->getData('_id');
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
        if($this->getId()) {
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
        Mage::log(__METHOD__);
        $newRealOrderId = Mage::helper('hackathon_ordertransaction')->getNewOrderIdFromSequence();
        $mongoOrder = clone $order;
        $mongoOrder->setId($newRealOrderId)
            ->unsetData('customer')
            ->unsetData('quote');
        $order->setId($newRealOrderId);

        $quoteId = $order->getQuoteId();
        $this->_loadQuoteByQuoteId($quoteId);

        $data = array(
            'state' => 'order',
            'order' => $mongoOrder->getData()
        );
        return $this->_updateOrder($data);
    }

    public function saveOrderItem(Mage_Sales_Model_Order_Item $orderItem)
    {
        Mage::log(__METHOD__);
        $newOrderItemId = Mage::helper('hackathon_ordertransaction')->getNewOrderItemIdFromSequence();
        $mongoOrderItem = clone $orderItem;
        $mongoOrderItem->setId($newOrderItemId)
            ->unsetData('order')
            ->unsetData('product');
        $orderItem->setId($newOrderItemId);

        $quoteId = $orderItem->getOrder()->getQuoteId();
        $this->_loadQuoteByQuoteId($quoteId);

        $orderItems = (array) $this->getData('order_items');
        $orderItems[] = $mongoOrderItem->getData();
        $data = array('order_items' => $orderItems);

        return $this->_updateOrder($data);
    }

    public function saveOrderPayment(Mage_Sales_Model_Order_Payment $orderPayment)
    {
        Mage::log(__METHOD__);
        $newOrderPaymentId = Mage::helper('hackathon_ordertransaction')->getNewOrderPaymentIdFromSequence();
        $mongoOrderPayment = clone $orderPayment;
        $mongoOrderPayment->setId($newOrderPaymentId)
            ->unsetData('order')
            ->unsetData('method_instance');
        $orderPayment->setId($newOrderPaymentId);

        $quoteId = $orderPayment->getOrder()->getQuoteId();
        $this->_loadQuoteByQuoteId($quoteId);

        $orderPayments = (array) $this->getData('order_payments');
        $orderPayments[] = $mongoOrderPayment->getData();
        $data = array('order_payments' => $orderPayments);

        return $this->_updateOrder($data);
    }

    public function saveOrderStatusHistory(Mage_Sales_Model_Order_Status_History $orderHistory)
    {
        Mage::log(__METHOD__);
        $newOrderStatusHistoryId = Mage::helper('hackathon_ordertransaction')->getNewOrderStatusHistoryIdFromSequence();
        $mongoOrderHistory = clone $orderHistory;
        $mongoOrderHistory->setId($newOrderStatusHistoryId)
            ->unsetData('order');
        $orderHistory->setId($newOrderStatusHistoryId);

        $quoteId = $orderHistory->getOrder()->getQuoteId();
        $this->_loadQuoteByQuoteId($quoteId);

        $orderStatuses = (array) $this->getData('order_status_histories');
        $orderStatuses[] = $mongoOrderHistory->getData();
        $data = array('order_status_histories' => $orderStatuses);

        return $this->_updateOrder($data);
    }

    public function saveOrderAddress(Mage_Sales_Model_Order_Address $orderAddress)
    {
        Mage::log(__METHOD__);
        $newOrderAddressId = Mage::helper('hackathon_ordertransaction')->getNewOrderAddressIdFromSequence();
        $mongoOrderAddress = clone $orderAddress;
        $mongoOrderAddress->setId($newOrderAddressId)
            ->unsetData('order');
        $orderAddress->setId($newOrderAddressId);

        $quoteId = $orderAddress->getOrder()->getQuoteId();
        $this->_loadQuoteByQuoteId($quoteId);

        $orderAddresses = array();
        foreach ((array) $this->getData('order_addresses') as $item)
        {
            $item = Mage::getModel('sales/order_address')->setData($item);
            if ($item->getAddressType() == $mongoOrderAddress->getAddressType())
            {
                $orderAddresses[] = $mongoOrderAddress->getData();
            }
            else
            {
                $orderAddresses[] = $item->getData();
            }
        }
        if (empty($orderAddresses))
        {
            // First address added
            $orderAddresses[] = $mongoOrderAddress->getData();
        }
        $data = array('order_addresses' => $orderAddresses);

        return $this->_updateOrder($data);
    }

    public function loadOrder($id, $field)
    {
        return $this->_loadOrderByModelType('order', $id, $field, false);
    }

    public function loadOrderItem($id, $field)
    {
        return $this->_loadOrderByModelType('order_items', $id, $field);
    }

    public function loadOrderAddress($id, $field)
    {
        return $this->_loadOrderByModelType('order_addresses', $id, $field);
    }

    public function loadOrderPayment($id, $field)
    {
        return $this->_loadOrderByModelType('order_payments', $id, $field);
    }

    public function loadOrderStatusHistory($id, $field)
    {
        return $this->_loadOrderByModelType('order_status_histories', $id, $field);
    }

    protected function _loadQuoteByQuoteId($quoteId)
    {
        if ($this->getQuoteId() != $quoteId)
        {
            $this->loadQuote($quoteId);
            if (! $this->getId())
            {
                Mage::throwException(
                    Mage::helper('hackathon_ordertransaction')->__('No associated quote with ID %s found in mongoDb', $quoteId)
                );
            }
        }
        return $this;
    }

    protected function _updateOrder(array $data)
    {
        Mage::log(array(array('_id' => new MongoId($this->getId())), array('$set' => $data)));
        $this->_tblSales->update(array('_id' => new MongoId($this->getId())), array('$set' => $data));
        $this->addData($data);
        return $this;
    }

    protected function _loadOrderByModelType($type, $id, $field, $isArray = true)
    {
        $this->setData(array());

        if ($isArray)
        {
            $cond = array($type => array($field => $id));
            // 'oder_items' => array('entity_id' => 8)
            //$this->_tblSales->ensureIndex("$type.$field");
        }
        else
        {
            // 'oder_items.entity_id => 8
            $this->_tblSales->ensureIndex("$type.$field");
            $cond = array("$type.$field" => "$id");
        }
        $result = $this->_tblSales->findOne($cond);
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
            if($this->getId()) {
                $this->setState('deleted')
                    ->updateQuote();
            }
        return $this;
    }

    public function updateQuote() {
        if($this->getId()) {
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
