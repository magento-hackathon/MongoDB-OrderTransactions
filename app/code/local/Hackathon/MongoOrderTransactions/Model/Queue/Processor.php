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
            $quote = Mage::getModel('sales/quote');
            $quote->setStoreId($order->getStoreId());
            $quote->setCustomerEmail($order->getCustomerEmail());
            
            // Do we need to construct a product to add it?
            foreach ($order->getAllItems() as $item) {
                $quote->addProduct($item);
            }

            $addressData = array(
                'firstname' => 'Test',
                'lastname' => 'Test',
                'street' => 'Sample Street 10',
                'city' => 'Somewhere',
                'postcode' => '12345',
                'telephone' => '1234567890',
                'country_id' => 'DE',
                'region_id' => 80,
            );

            $billingAddress = $quote->getBillingAddress()->addData($addressData);
            $shippingAddress = $quote->getShippingAddress()->addData($addressData);

            $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod('flatrate_flatrate')
                ->setPaymentMethod('checkmo');

            $serviceQuote = Mage::getModel('sales/service_quote', $quote);
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
