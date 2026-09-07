Feature: Internal order notes
    In order to share order handling instructions with other administrators
    As an administrator
    I can maintain an internal note on an order

    Background:
        Given I am an administrator viewing an order

    Scenario: Add and edit a note
        When I save the note "Fragile parcel"
        Then the stored note should be "Fragile parcel"
        When I save the note "Call before delivery"
        Then the stored note should be "Call before delivery"

    Scenario: Delete a note
        When I save the note "Temporary note"
        And I delete the note
        Then the order should have no note

    Scenario: Reject an overly long note
        When I save the note "Keep this note"
        And I submit a note with 501 characters
        Then the stored note should be "Keep this note"

    Scenario: Reject an invalid CSRF token
        When I save the note "Keep this note"
        And I submit a note with an invalid CSRF token
        Then the stored note should be "Keep this note"
