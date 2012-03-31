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
     * @return null
     **/
    public function merge()
    {
        $quotes = Mage::getModel('hackathon_ordertransaction/mongo')->getQuotes();
        foreach ($quotes as $quote) {
            try {
                // Update Mongo
                $mongo = Mage::getModel('hackathon_ordertransaction/mongo');
                $mongo->setToDelete($quote['quote_id']);
                $persistentOrder = Mage::getResourceModel('sales/order');
                $persistentOrder->setData($quote['order']);
                $persistentOrder->save();
            } catch (Exception $e) {
                $mongo->revertToOrder($quote['quote_id']);
            }
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
            Mage::getModel('hackathon_ordertransaction/mongo')->clean();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
}
