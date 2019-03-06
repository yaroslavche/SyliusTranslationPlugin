@translation
Feature: Translation ui in admin section
    In order to empower custom translations
    As an Administrator and without accessibility to change translations files
    I want to have ui-based possibility to change any of translation messages

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    Scenario: set translation message
        Given I send request to set locale "localeCode" translation "translation" for id "id" in domain "domain"
        Then locale "localeCode" translation for id "id" in domain "domain" should be "translation"

#        Given I send request to set locale "null" translation "translation" for id "id" in domain "domain"
#        Then last response should be error with text "Locale must be set"
#
#        Given I send request to set locale "localeCode" translation "translation" for id "id" in domain "null"
#        Then last response should be error with text "Domain must be set"
#
#        Given I send request to set locale "localeCode" translation "translation" for id "null" in domain "domain"
#        Then last response should be error with text "Id must be set"
#
#        Given I send request to set locale "localeCode" translation "null" for id "id" in domain "domain"
#        Then locale "localeCode" translation for id "id" in domain "domain" should be "empty"