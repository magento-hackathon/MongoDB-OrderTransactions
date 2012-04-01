<?php

class Hackathon_MongoOrderTransactions_Model_Observer
{

    /**
     * @var array of qutoe items to reindex
     */
    protected $_reindexProducts = array();

    protected $_count = 0;

    /**
     * Updates the stock quantity for the item in MongoDB.
     *
     * @param Varien_Event_Observer $event
     * @return Hackathon_MongoOrderTransactions_Model_Observer
     */
    public function checkoutCartSaveAfter(Varien_Event_Observer $observer)
    {
        //@TODO TODO remove function
        return $this;

        $quote = $observer->getCart()->getQuote();

        $mongodb = Mage::getSingleton('hackathon_ordertransaction/mongo');
        $mongodb->loadQuote($quote->getId());
		$mongodb->setQuoteId($quote->getId());
        $mongodb->setItems(array());
        foreach($quote->getAllItems() as $item) {
            $mongodb->addItem($item->getProductId(), $item->getQty());
        }
		$mongodb->saveQuote();


        return $this;
    }

    /**
     * check wether in mongodb has a different cart qty
     * 
     * @return various qty difference or bool false
     */
    private function differncetoMongo($quoteId,$items,$productId) {
        $mongodb = Mage::getSingleton('hackathon_ordertransaction/mongo');
        $mongodb->loadQuote($quoteId);
        if($mongodb->getItemByProductId($productId) != $items[$productId]['qty'])
            return $items[$productId]['qty'] - 1*$mongodb->getItemByProductId($productId);
        return false;
    }

     /**
     * check product qty: if no change (same in mongodb) do nothing :)
     * on change: store it in mongodb, decrease the mysql stock
     * after change: on error of mysql stock, roll back mongodb, error message
     *
     * @param  Varien_Event_Observer $observer
     * @return Mage_CatalogInventory_Model_Observer
     */
    public function checkQuoteItemQty($observer)
    {
        $quoteItem = $observer->getEvent()->getItem();
        /* @var $quoteItem Mage_Sales_Model_Quote_Item */
        if (!$quoteItem || !$quoteItem->getProductId() || !$quoteItem->getQuote()
            || $quoteItem->getQuote()->getIsSuperMode()) {
            return $this;
        }


        //get quote item quantities
        $items = $this->_getProductsQty($quoteItem->getQuote()->getAllItems());


        //compare to mongodb
        $diff = $this->differncetoMongo($quoteItem->getQuoteId(),$items,$quoteItem->getProductId());


            $this->_count = $this->_count + 1;
            if($this->_count > 4) {
                Mage::log('endles loop?');
                die('fuck');
            }

        Mage::log('product-id: '.$quoteItem->getProductId());
        Mage::log('qty: '.$items[$quoteItem->getProductId()]['qty']);
        Mage::log('diff: '.$diff);

        if($diff === false)
            //if there is no cart product change, we are done here
            return $this;
        else {
            //adust mongodb qty
            $mongodb = Mage::getSingleton('hackathon_ordertransaction/mongo');
            $mongodb->loadQuote($quoteItem->getQuoteId());
            $mongodb->setQuoteId($quoteItem->getQuoteId());
            $mongodb->addItem($quoteItem->getProductId(),$items[$quoteItem->getProductId()]['qty'])
                ->saveQuote();
        }

        //change the product stock
        if($diff < 0) {
            //increase catalog_inventory
            Mage::log('product-id'.$quoteItem->getProductId());
            Mage::log('abs-qty:'.print_r(abs($diff),true));
            Mage::getSingleton('cataloginventory/stock')->backItemQty($quoteItem->getProductId(), abs($diff));

Mage::log('back item qty:'.$diff);
            //@TODO TODO reindex needed?
        } else {

            $itemChanges = array(
                    $quoteItem->getProductId() => $items[$quoteItem->getProductId()]
                );
            $itemChanges[$quoteItem->getProductId()]['qty'] = $diff;

Mage::log('decreased mysql stock by '.$diff);
            
            //decrease stock
            $this->_reindexProducts = array_merge($this->_reindexProducts,Mage::getSingleton('cataloginventory/stock')->registerProductsSale($itemChanges));
        }


        return $this;




        /**
         * Get Qty
         */
        $qty = $quoteItem->getQty();

        /**
         * Check item for options
         */
        if (($options = $quoteItem->getQtyOptions()) && $qty > 0) {
            $qty = $quoteItem->getProduct()->getTypeInstance(true)->prepareQuoteItemQty($qty, $quoteItem->getProduct());
            $quoteItem->setData('qty', $qty);

            $stockItem = $quoteItem->getProduct()->getStockItem();
            if ($stockItem) {
                $result = $stockItem->checkQtyIncrements($qty);
                if ($result->getHasError()) {
                    $quoteItem->addErrorInfo(
                        'cataloginventory',
                        Mage_CatalogInventory_Helper_Data::ERROR_QTY_INCREMENTS,
                        $result->getMessage()
                    );

                    $quoteItem->getQuote()->addErrorInfo(
                        $result->getQuoteMessageIndex(),
                        'cataloginventory',
                        Mage_CatalogInventory_Helper_Data::ERROR_QTY_INCREMENTS,
                        $result->getQuoteMessage()
                    );
                } else {
                    // Delete error from item and its quote, if it was set due to qty problems
                    $this->_removeErrorsFromQuoteAndItem(
                        $quoteItem,
                        Mage_CatalogInventory_Helper_Data::ERROR_QTY_INCREMENTS
                    );
                }
            }

            foreach ($options as $option) {
                $optionValue = $option->getValue();
                /* @var $option Mage_Sales_Model_Quote_Item_Option */
                $optionQty = $qty * $optionValue;
                $increaseOptionQty = ($quoteItem->getQtyToAdd() ? $quoteItem->getQtyToAdd() : $qty) * $optionValue;

                $stockItem = $option->getProduct()->getStockItem();

                if ($quoteItem->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
                    $stockItem->setProductName($quoteItem->getName());
                }

                /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
                if (!$stockItem instanceof Mage_CatalogInventory_Model_Stock_Item) {
                    Mage::throwException(
                        Mage::helper('cataloginventory')->__('The stock item for Product in option is not valid.')
                    );
                }

                /**
                 * define that stock item is child for composite product
                 */
                $stockItem->setIsChildItem(true);
                /**
                 * don't check qty increments value for option product
                 */
                $stockItem->setSuppressCheckQtyIncrements(true);

                $qtyForCheck = $this->_getQuoteItemQtyForCheck(
                    $option->getProduct()->getId(),
                    $quoteItem->getId(),
                    $increaseOptionQty
                );

                $result = $stockItem->checkQuoteItemQty($optionQty, $qtyForCheck, $optionValue);

                if (!is_null($result->getItemIsQtyDecimal())) {
                    $option->setIsQtyDecimal($result->getItemIsQtyDecimal());
                }

                if ($result->getHasQtyOptionUpdate()) {
                    $option->setHasQtyOptionUpdate(true);
                    $quoteItem->updateQtyOption($option, $result->getOrigQty());
                    $option->setValue($result->getOrigQty());
                    /**
                     * if option's qty was updates we also need to update quote item qty
                     */
                    $quoteItem->setData('qty', intval($qty));
                }
                if (!is_null($result->getMessage())) {
                    $option->setMessage($result->getMessage());
                    $quoteItem->setMessage($result->getMessage());
                }
                if (!is_null($result->getItemBackorders())) {
                    $option->setBackorders($result->getItemBackorders());
                }

                if ($result->getHasError()) {
                    $option->setHasError(true);

                    $quoteItem->addErrorInfo(
                        'cataloginventory',
                        Mage_CatalogInventory_Helper_Data::ERROR_QTY,
                        $result->getQuoteMessage()
                    );

                    $quoteItem->getQuote()->addErrorInfo(
                        $result->getQuoteMessageIndex(),
                        'cataloginventory',
                        Mage_CatalogInventory_Helper_Data::ERROR_QTY,
                        $result->getQuoteMessage()
                    );
                } else {
                    // Delete error from item and its quote, if it was set due to qty lack
                    $this->_removeErrorsFromQuoteAndItem($quoteItem, Mage_CatalogInventory_Helper_Data::ERROR_QTY);
                }

                $stockItem->unsIsChildItem();
            }
        } else {
            $stockItem = $quoteItem->getProduct()->getStockItem();
            /* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
            if (!$stockItem instanceof Mage_CatalogInventory_Model_Stock_Item) {
                Mage::throwException(Mage::helper('cataloginventory')->__('The stock item for Product is not valid.'));
            }

            /**
             * When we work with subitem (as subproduct of bundle or configurable product)
             */
            if ($quoteItem->getParentItem()) {
                $rowQty = $quoteItem->getParentItem()->getQty() * $qty;
                /**
                 * we are using 0 because original qty was processed
                 */
                $qtyForCheck = $this->_getQuoteItemQtyForCheck(
                    $quoteItem->getProduct()->getId(),
                    $quoteItem->getId(),
                    0
                );
            } else {
                $increaseQty = $quoteItem->getQtyToAdd() ? $quoteItem->getQtyToAdd() : $qty;
                $rowQty = $qty;
                $qtyForCheck = $this->_getQuoteItemQtyForCheck(
                    $quoteItem->getProduct()->getId(),
                    $quoteItem->getId(),
                    $increaseQty
                );
            }

            $productTypeCustomOption = $quoteItem->getProduct()->getCustomOption('product_type');
            if (!is_null($productTypeCustomOption)) {
                // Check if product related to current item is a part of grouped product
                if ($productTypeCustomOption->getValue() == Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE) {
                    $stockItem->setIsChildItem(true);
                }
            }

            $result = $stockItem->checkQuoteItemQty($rowQty, $qtyForCheck, $qty);

            if ($stockItem->hasIsChildItem()) {
                $stockItem->unsIsChildItem();
            }

            if (!is_null($result->getItemIsQtyDecimal())) {
                $quoteItem->setIsQtyDecimal($result->getItemIsQtyDecimal());
                if ($quoteItem->getParentItem()) {
                    $quoteItem->getParentItem()->setIsQtyDecimal($result->getItemIsQtyDecimal());
                }
            }

            /**
             * Just base (parent) item qty can be changed
             * qty of child products are declared just during add process
             * exception for updating also managed by product type
             */
            if ($result->getHasQtyOptionUpdate()
                && (!$quoteItem->getParentItem()
                    || $quoteItem->getParentItem()->getProduct()->getTypeInstance(true)
                        ->getForceChildItemQtyChanges($quoteItem->getParentItem()->getProduct())
                )
            ) {
                $quoteItem->setData('qty', $result->getOrigQty());
            }

            if (!is_null($result->getItemUseOldQty())) {
                $quoteItem->setUseOldQty($result->getItemUseOldQty());
            }
            if (!is_null($result->getMessage())) {
                $quoteItem->setMessage($result->getMessage());
                if ($quoteItem->getParentItem()) {
                    $quoteItem->getParentItem()->setMessage($result->getMessage());
                }
            }

            if (!is_null($result->getItemBackorders())) {
                $quoteItem->setBackorders($result->getItemBackorders());
            }

            if ($result->getHasError()) {
                $quoteItem->addErrorInfo(
                    'cataloginventory',
                    Mage_CatalogInventory_Helper_Data::ERROR_QTY,
                    $result->getMessage()
                );

                $quoteItem->getQuote()->addErrorInfo(
                    $result->getQuoteMessageIndex(),
                    'cataloginventory',
                    Mage_CatalogInventory_Helper_Data::ERROR_QTY,
                    $result->getQuoteMessage()
                );
            } else {
                // Delete error from item and its quote, if it was set due to qty lack
                $this->_removeErrorsFromQuoteAndItem($quoteItem, Mage_CatalogInventory_Helper_Data::ERROR_QTY);
            }
        }

        return $this;
    }

    /**
     * wrapper to get observer object to call catalog inventory
     * like create_order does from quote to order placement
     *
     * @param  Varien_Event_Observer $observer
     * @return Hackathon_MongoOrderTransactions_Model_Observer
     */
    public function reindexQuoteInventory($observer) {
        if(count($this->_reindexProducts) > 0) {
            $productIds = array();
            foreach ($this->_reindexProducts as $item) {
                $item->save();
                $productIds[] = $item->getProductId();
            }
            Mage::getResourceSingleton('cataloginventory/indexer_stock')->reindexProducts($productIds);
            Mage::getResourceSingleton('catalog/product_indexer_price')->reindexProductIds($productIds);
        }

        return $this;
    }


    //copied from Mage_CatalogInventory_Model_Observer

    /**
     * Adds stock item qty to $items (creates new entry or increments existing one)
     * $items is array with following structure:
     * array(
     *  $productId  => array(
     *      'qty'   => $qty,
     *      'item'  => $stockItems|null
     *  )
     * )
     *
     * @param Mage_Sales_Model_Quote_Item $quoteItem
     * @param array &$items
     */
    protected function _addItemToQtyArray($quoteItem, &$items)
    {
        $productId = $quoteItem->getProductId();
        if (!$productId)
            return;
        if (isset($items[$productId])) {
            $items[$productId]['qty'] += $quoteItem->getTotalQty();
        } else {
            $stockItem = null;
            if ($quoteItem->getProduct()) {
                $stockItem = $quoteItem->getProduct()->getStockItem();
            }
            $items[$productId] = array(
                'item' => $stockItem,
                'qty'  => $quoteItem->getTotalQty()
            );
        }
    }

    //copied from Mage_CatalogInventory_Model_Observer

    /**
     * Prepare array with information about used product qty and product stock item
     * result is:
     * array(
     *  $productId  => array(
     *      'qty'   => $qty,
     *      'item'  => $stockItems|null
     *  )
     * )
     * @param array $relatedItems
     * @return array
     */
    protected function _getProductsQty($relatedItems)
    {
        $items = array();
        foreach ($relatedItems as $item) {
            $productId  = $item->getProductId();
            if (!$productId) {
                continue;
            }
            $children = $item->getChildrenItems();
            if ($children) {
                foreach ($children as $childItem) {
                    $this->_addItemToQtyArray($childItem, $items);
                }
            } else {
                $this->_addItemToQtyArray($item, $items);
            }
        }
        return $items;
    }


}
