Feature: Using API-Platform, I can get companies, but not create/update/delete them.

  Scenario: I can get a list of companies
    Given there are companies
    When I get a list of companies
    Then I see a list of companies

  Scenario: I cannot create a company
    When I send a POST request to "/companies"
    Then the method is not allowed

  Scenario: I can get a company
    Given there is a company
    When I get a company
    Then I see a company

  @debug
  Scenario: I can print a company item schema
    When print company item JSON schema

  @debug
  Scenario: I can print a company collection schema
    When print company list JSON schema

  @debug
  Scenario: I can print the last company request
    When I create a company
    Then print last JSON request
