Feature: I can execute Behat tests with current API Extension

  Scenario: I can execute Behat tests
    When I run "behat --no-colors features/monkey.feature"
    Then it should pass with:
    """
    1 scenario (1 passed)
    """
