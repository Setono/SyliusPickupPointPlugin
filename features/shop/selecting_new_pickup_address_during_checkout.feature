@shop_shipping_pickup
Feature: Selecting new pickup address during the checkout
    In order to conveniently pickup my delivery
    As a customer
    I want to select a new pickup address from a list of available pickup places during checkout

    Background:
        Given the store operates on a single channel in "United States"
        And the store has a product "PHP T-Shirt"
        And the store has "DHL" shipping method with "$5.00" fee
        And the store has "GLS" shipping method with "$5.00" fee
        And the store has "PostNord" shipping method with "$5.00" fee
        And shipping method "GLS" has the selected "GLS" pickup point provider
        And shipping method "Post_Nord" has the selected "Post_Nord" pickup point provider

    @ui @javascript
    Scenario: Selecting shipping provider and choosing shipping point
        Given I have product "PHP T-Shirt" in the cart
        When I am at the checkout addressing step
        And I specify the email as "mail@mail.com"
        And I specify the shipping address as "Fifth Avenue", "New York", "12342", "United States" for "John John"
        And I complete the addressing step
        And I select "GLS" pickup point shipping method
        And I choose the first option
        And I complete the shipping step
        Then the shipping method should have a pickup point
