MongoDB-OrderTransactions
=========================

Hackathon Team
--------------
Anthony, Andreas, Rouven, Allistair, Vinai, Matthias

About this project
------------------
**Important: this code is a proof of concept and in no way meant to be used in production!**

You can scale Magento in many ways but there is one bottleneck which won't go away: the checkout process.
When an order is placed, Magento does a lot of work in the background. This poses a problem for high volume stores.

How it works
------------
This extension (part of the Magento Hackathon Munich in March 2012) uses a MongoDB database to solve the issue.
The basic idea is to not create the orders directly on checkout but to push the order creation task to a queue which is processed by a cron job. The customer gets the confirmation as soon as the data data is written to the queue which should speed up the checkout quite a bit.

To make this possible, the following aspects have to be considered and implemented:

- save the quotes information in MongoDB and keep it up-to-date whenever the customer changes his cart.
- check if there are enough articles in stock when a user adds product to the cart.
- after the checkout, transform the quote to an order in MongoDB, create a job to insert the orders into the Magento database
- remove old entries (completed jobs etc.) in the MongoDB database periodically.
- Magento uses the order as a foreign key in several flat tables and the order addresses. Therefore the creation of this entries has to be pushed to the queue as well.

Requirements
------------
- **MongoDB**. Obviously. Find out how to install it in the [MongoDB documentation](http://www.mongodb.org/display/DOCS/Quickstart).
- **MongoDB PHP driver**. This is a PECL extension. Check the [installation guide](http://www.php.net/manual/en/mongo.installation.php).

Todo
----
- Fix Mage_Checkout_Model_Type_Multishipping::createOrders()
  It doesn't use sales/service_quote::submitAll() or sales/service_quote::submitOrder()
- Update MongoDB when user logs in (quote data is not updated correctly when you add products as guest and then log in?)
