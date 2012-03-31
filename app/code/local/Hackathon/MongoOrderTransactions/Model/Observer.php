<?php

class Hackathon_MongoOrderTransactions_Model_Observer
{
    /**
     * Updates the stock quantity for the item in MongoDB.
     *
     * @param Varien_Event_Observer $event
     * @return Hackathon_MongoOrderTransactions_Model_Observer
     */
    public function checkoutCartProductAddAfter(Varien_Event_Observer $observer)
    {
    	$quoteItem = $observer->getEvent()->getQuoteItem();
		
        $mongodb = Mage::getModel('hackathon_ordertransaction/mongo');
        $mongodb->loadQuote($quoteItem->getQuote()->getId());
		$mongodb->setQuoteId($quoteItem->getQuote()->getId());
        $mongodb->addItem($observer->getProduct()->getId(), $quoteItem->getQty());
		$mongodb->saveQuote();
		
        return $this;
    }
}
