<?php
/**
*
*/
class Hackathon_MongoOrderTransactions_Model_Queue_Processor
{
    /**
     * Select all the Mongo DB orders and merge them to the
     * MySQL DB
     *
     * @return void
     **/
    public function merge()
    {
        try {
            // @TODO retrieve and merge the data
            $order = Mage::getModel('hackathon_ordertransaction/order');
            // Turn the result array into a single DB insert?
            $serviceQuote = Mage::getModel('sales/service_quote');
            $serviceQuote->setOrderData($order->getData());
            $serviceQuote->submitAll();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Select all the Mongo DB orders that have been previously
     * merged and clean the documents.
     *
     * @return null
     **/
    public function clean()
    {
        try {
            Mage::getModel('hackathon_ordertransaction/order')->deleteProcessedOrders();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
