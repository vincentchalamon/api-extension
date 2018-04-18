Feature: Using API-Platform, I can get the API documentation.

  Scenario Outline: I can get the API documentation in the required format
    When I get the API doc in <format>
    Then I see the API doc in <format>
    Examples:
      | format |
      | json   |
      | jsonld |
      | html   |
