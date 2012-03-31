<?php

class Hackathon_MongoOrderTransactions_Model_Observer
{
    /**
     * Observer on checkout_cart_product_add_after to update the stock quantty for the item
     * in MongoDB.
     *
     * @param Varien_Event_Observer $event
     */
    public function checkoutCartProductAddAfter( Varien_Event_Observer $observer )
    {
        $event = $observer->getEvent();
        $quoteItem = $event->getQuoteItem();
        $product = $event->getProduct();

        

        return $this;
    }
}
