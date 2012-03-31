<?php

class Hackathon_MongoOrderTransactions_Model_Observer
{

    /**
     * Updates the stock quantity for the item in MongoDB.
     *
     * @param Varien_Event_Observer $event
     * @return Hackathon_MongoOrderTransactions_Model_Observer
     */
    public function checkoutCartSaveAfter(Varien_Event_Observer $observer)
    {
        $quote = $observer->getCart()->getQuote();

        $mongodb = Mage::getModel('hackathon_ordertransaction/mongo');
        $mongodb->loadQuote($quote->getId());
		$mongodb->setQuoteId($quote->getId());
        $mongodb->setItems(array());
        foreach($quote->getAllItems() as $item) {
            $mongodb->addItem($item->getProductId(), $item->getQty());
        }
		$mongodb->saveQuote();

        return $this;
    }




}
