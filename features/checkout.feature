Feature: Checkout
    In order to maximise site performance
    As a website user
    I need the product page to be save to a Mongo DB without causing error

    @javascript
    Scenario: Anonymous puchase of 1 product
        Given I am on "/electronics/cell-phones/nokia-2610-phone.html"
        And I press "Add to Cart"
        Then the "ul.messages li.success-msg span" element should contain "Nokia 2610 Phone was added to your shopping cart."
        # Then the "h1" element should contain "Shopping Cart"
        And I press "Proceed to Checkout"
        Then the "h1" element should contain "Checkout"
        # Guest Checkout
        And I check "login:guest"
        And I press "onepage-guest-register-button"
        # Billing Details
        And I fill in "billing:firstname" with "Test"
        And I fill in "billing:lastname" with "User"
        And I fill in "billing:email" with "testuser@local.com"
        And I fill in "billing:street1" with "Address Street 1"
        And I fill in "billing:city" with "City"
        And I fill in "billing:postcode" with "W1 1DS"
        And I select "GB" from "billing:country_id"
        And I fill in "billing:telephone" with "555118118"
        Then the "billing:use_for_shipping_yes" checkbox should be checked
        Then I should see an "div#billing-buttons-container button" element
        # And I wait for 5000 milliseconds
        And I click the "div#billing-buttons-container button" element
        # Shipping Method
        Then I should see an "div#shipping-method-buttons-container button" element
        And I wait for 5000 milliseconds
        And I click the "div#shipping-method-buttons-container button" element
        # Payment Method
        # And I wait for 5000 milliseconds
        Then I should see an "div#payment-buttons-container button" element
        And I wait for 5000 milliseconds
        And I check "p_method_checkmo"
        And I click the "div#payment-buttons-container button" element
        # Confirm Order
        And I wait for 5000 milliseconds
        Then I should see an "div#review-buttons-container button" element
        And I click the "div#review-buttons-container button" element
        And I wait for 5000 milliseconds
        Then the "h2" element should contain "Thank you for your purchase!"
        # Continue Shopping 
        And I press "Continue Shopping"
        Then I should be on "/"