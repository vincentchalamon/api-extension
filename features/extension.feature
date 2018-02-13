Feature: I can execute Behat tests with current API Extension

  Scenario: I can execute Behat tests
    When I run "behat --no-colors features/monkey.feature"
    Then it should pass with:
    """
    1 scenario (1 passed)
    """

  Scenario: Scenario state should be reset between scenarios
    When I run "behat --no-colors features/donkeys.feature"
    Then it should fail with:
    """
    [Behat\Testwork\Argument\Exception\UnknownParameterValueException]
    """

  Scenario: Scenario Outline should work properly
    When I run "behat --no-colors features/bandar-log.feature"
    Then it should pass with:
    """
    2 scenarios (2 passed)
    """
