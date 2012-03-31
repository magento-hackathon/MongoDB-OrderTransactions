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
        $mongo = Mage::getModel('hackathon_ordertransaction/mongo');
        $quotes = $mongo->getQuotes();
        foreach ($quotes as $quote) {
            try {
                $mongo->setToDelete($quote['quote_id']);
                $persistentOrder = Mage::getResourceModel('sales/order');
                $persistentOrder->setData($quote['order']);
                $persistentOrder->save();
                unset($persistentOrder);
            } catch (Exception $e) {
                $mongo->revertToOrder($quote['quote_id']);
                Mage::logException($e);
            }
        }
        unset($quotes, $mongo);
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
