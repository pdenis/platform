Feature: Settings label must be clickable

  Scenario: Click by settings label must change status of the appropriate checkbox
    Given I login as administrator
    And I go to System/ Configuration
    And I click "Localization" on configuration sidebar
    And uncheck Use Default for "Format address per country" field
    When I uncheck "Format address per country"
    Then the "Format address per country" checkbox should not be checked
