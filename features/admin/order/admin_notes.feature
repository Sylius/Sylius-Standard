@admin @ui
Feature: Managing admin notes for orders
  In order to keep internal notes about orders
  As an administrator
  I want to be able to add, edit and view admin notes for orders

  Background:
    Given I am logged in as an administrator
    And there is a customer "john.doe@example.com"
    And there is a product "Test Product" with code "TEST-PRODUCT"
    And there is an order "#000001" placed by customer "john.doe@example.com" with product "Test Product"

  Scenario: Administrator can view admin notes section on order show page
    When I am on the order "#000001" details page
    Then I should see the admin notes section
    And the admin notes section should be empty

  Scenario: Administrator can add admin notes to an order
    When I am on the order "#000001" details page
    And I click "Edit" button in admin notes section
    And I fill in "adminNotes" with "This is a test admin note"
    And I save the form
    Then I should be on the order "#000001" details page
    And I should see "This is a test admin note" in admin notes section

  Scenario: Administrator can edit existing admin notes
    Given the order "#000001" has admin notes "Initial note"
    When I am on the order "#000001" details page
    And I click "Edit" button in admin notes section
    And I fill in "adminNotes" with "Updated admin note"
    And I save the form
    Then I should be on the order "#000001" details page
    And I should see "Updated admin note" in admin notes section

  Scenario: Administrator can delete admin notes
    Given the order "#000001" has admin notes "Note to be deleted"
    When I am on the order "#000001" details page
    And I click "Edit" button in admin notes section
    And I clear the "adminNotes" field
    And I save the form
    Then I should be on the order "#000001" details page
    And the admin notes section should be empty

  Scenario: Administrator cannot add admin notes longer than 500 characters
    When I am on the order "#000001" details page
    And I click "Edit" button in admin notes section
    And I fill in "adminNotes" with a string of 501 characters
    And I save the form
    Then I should see validation error "Notatka nie może być dłuższa niż 500 znaków"

  Scenario: Admin notes are truncated to 500 characters when set programmatically
    When I set admin notes "A very long note that exceeds 500 characters limit. This is a test to verify that when admin notes are set programmatically with a string longer than 500 characters, they are automatically truncated to the maximum allowed length of 500 characters. This ensures data integrity and prevents database errors. The truncation should happen silently without throwing any errors, and the first 500 characters should be preserved exactly as they were entered, with any characters beyond that limit being discarded. This is an important feature for maintaining data consistency and preventing potential issues with overly long notes that could cause problems in the system. Let's make sure this string is definitely longer than 500 characters by adding more text here. We need to reach at least 501 characters to properly test the truncation functionality. Here is some additional text to ensure we exceed the limit." to order "#000001"
    And I am on the order "#000001" details page
    Then the admin notes should be truncated to 500 characters

