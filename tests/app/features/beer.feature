Feature: Using API-Platform, I can get, create, update & delete beers.

  Scenario: I can get a list of beers
    Given there are beers
    When I get a list of beers
    Then I see a list of beers

  Scenario: I can get a list of beers filtered by name
    Given there are beers
    When I get a list of beers filtered by name=Chouffe
    Then I don't see any beer

  Scenario: I can create a beer
    When I create a beer
    Then I see a beer

  Scenario: I can create a beer with images
    Given there are 3 images
    When I create a beer with:
      | images  |
      | 1, 2, 3 |
    Then I see a beer

  Scenario: I can create a beer
    When I create a beer with:
      | name    |
      | Chouffe |
    Then I see a beer

  Scenario: I can update a beer
    Given there is a beer
    When I update a beer
    Then I see a beer

  Scenario: I can update a beer and fill its new name
    Given there is a beer
    When I update a beer with:
      | name    |
      | Chouffe |
    Then I see a beer

  Scenario: I can update a beer by its name
    Given the following beer:
      | name    |
      | Chouffe |
    When I update the beer Chouffe
    Then I see a beer

  Scenario: I can update a beer by its name and fill its new name
    Given the following beer:
      | name    |
      | Chouffe |
    When I update the beer Chouffe with:
      | name |
      | Kwak |
    Then I see a beer

  Scenario: I can get a beer
    Given there is a beer
    When I get a beer
    Then I see a beer

  Scenario: I can get a beer by its name
    Given the following beer:
      | name    |
      | Chouffe |
    When I get the beer Chouffe
    Then I see a beer

  Scenario: I cannot get a non-existing beer
    When I send a GET request to "/beers/1"
    Then the beer is not found

  Scenario: I can delete a beer
    Given there is a beer
    When I delete a beer
    Then the beer has been successfully deleted

  Scenario: I can delete a beer by its name
    Given the following beer:
      | name    |
      | Chouffe |
    When I delete the beer Chouffe
    Then the beer has been successfully deleted

  @debug
  Scenario: I can print a beer item schema
    When print beer item JSON schema

  @debug
  Scenario: I can print a beer collection schema
    When print beer list JSON schema

  @debug
  Scenario: I can print the last beer request
    When I create a beer
    Then print last JSON request
