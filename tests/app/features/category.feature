Feature: Using API-Platform, I can get, create, update & delete categories.

  Scenario: I can get a list of categories
    Given there are categories
    When I get a list of categories
    Then I see a list of categories

  Scenario: I can get a list of categories filtered by name
    Given the following category:
      | name    |
      | Chouffe |
    When I get a list of categories filtered by name=Chouffe
    Then I don't see any category

  Scenario: I can create a category
    When I create a category
    Then I see a category

  Scenario: I can create a category
    When I create a category with:
      | name    |
      | Chouffe |
    Then I see a category

  Scenario: I can update a category
    Given there is a category
    When I update a category
    Then I see a category

  Scenario: I can update a category and fill its new name
    Given there is a category
    When I update a category with:
      | name    |
      | Chouffe |
    Then I see a category

  Scenario: I can update a category by its name
    Given the following category:
      | name    |
      | Chouffe |
    When I update the category Chouffe
    Then I see a category

  Scenario: I can update a category by its name and fill its new name
    Given the following category:
      | name    |
      | Chouffe |
    When I update the category Chouffe with:
      | name |
      | Kwak |
    Then I see a category

  Scenario: I can get a category
    Given there is a category
    When I get a category
    Then I see a category

  Scenario: I can get a category by its name
    Given the following category:
      | name    |
      | Chouffe |
    When I get the category Chouffe
    Then I see a category

  Scenario: I cannot get a non-existing category
    When I send a GET request to "/categories/1"
    Then the category is not found

  Scenario: I can delete a category
    Given there is a category
    When I delete a category
    Then the category has been successfully deleted

  Scenario: I can delete a category by its name
    Given the following category:
      | name    |
      | Chouffe |
    When I delete the category Chouffe
    Then the category has been successfully deleted

  @debug
  Scenario: I can print a category item schema
    When print category item JSON schema

  @debug
  Scenario: I can print a category collection schema
    When print category list JSON schema

  @debug
  Scenario: I can print the last category request
    When I create a category
    Then print last JSON request
