@translationplugin
Feature: Translation ui in admin section
    In order to empower custom translations
    As an Administrator and without accessibility to change translations files
    I want to have ui-based possibility to change any of translation messages

    Background:
        Given the store operates on a single channel in "United States"
        And I am logged in as an administrator

    @ui @javascript
    Scenario Outline: Adding a custom translation messages
        Given I want to change translation for "<message_domain>" for "<locale>" with "<text>"
        Examples:
            | message_domain               | locale | text      |
            | sylius.ui.view_and_edit_cart | en     | View cart |

    # show plugin page
    # check invalid users, locales, texts
    # save history
    # notification?
