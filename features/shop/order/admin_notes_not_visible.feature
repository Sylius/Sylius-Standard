@shop
Feature: Admin notes are not visible in shop
  In order to protect internal information
  As a customer (logged in or guest)
  I should not be able to see admin notes in the shop

  Background:
    Given there is a customer "customer@example.com"
    And there is a product "Test Product" with code "TEST-PRODUCT"
    And there is an order "#000001" placed by customer "customer@example.com" with product "Test Product"
    And the order "#000001" has admin notes "Secret admin note"

  Scenario: Guest customer cannot see admin notes on order summary page
    When I am on the order "#000001" summary page with token
    Then I should not see "Secret admin note"
    And I should not see "admin notes" text

  Scenario: Logged in customer cannot see admin notes on their order page
    Given I am logged in as "customer@example.com"
    When I am on my order "#000001" page
    Then I should not see "Secret admin note"
    And I should not see "admin notes" text

  Scenario: Admin notes field is not present in shop order forms
    When I am on the order "#000001" summary page with token
    Then the form should not contain "adminNotes" field

