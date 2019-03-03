@translation
Feature: Translation ui in admin section
    In order to empower custom translations
    As an Administrator and without accessibility to change translations files
    I want to have ui-based possibility to change any of translation messages

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    Scenario: test
        Given that channel allows to shop using the "English" locale

    @ui @javascript
    Scenario: Get translation message
        Given I select translations for locale "en"
        And I select message domain "messages"
        Then translation for "sylius.ui.view_and_edit_cart" should be "Test"

    @ui @javascript
    Scenario: Modify translation messages
        Given I select translations for locale "en"
        And I select message domain "messages"
        And I change translation for "sylius.ui.view_and_edit_cart" with "Test"
        Then translation for "sylius.ui.view_and_edit_cart" should be "Test"
