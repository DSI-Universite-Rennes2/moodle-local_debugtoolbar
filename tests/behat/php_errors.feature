@local @local_debugtoolbar
Feature: Monitor PHP errors
  As an admin,
  I should be able to see if there are some PHP errors on the current page.

  Background:
    Given I log in as "admin"
    And the following config values are set as admin:
      | enable               | 1 | local_debugtoolbar |
      | enable_error_handler | 1 | local_debugtoolbar |

  Scenario: I see a PHP error
    When I visit "/local/debugtoolbar/tests/behat/fixtures/testcases.php?case=php-error"
    Then I should see "1" "error" in the debug toolbar

  Scenario: I see a PHP warning
    When I visit "/local/debugtoolbar/tests/behat/fixtures/testcases.php?case=php-warning"
    Then I should see "1" "warning" in the debug toolbar

  Scenario: I see a PHP deprecation
    When I visit "/local/debugtoolbar/tests/behat/fixtures/testcases.php?case=php-deprecated"
    Then I should see "1" "deprecation" in the debug toolbar

  Scenario: I see a PHP notice
    When I visit "/local/debugtoolbar/tests/behat/fixtures/testcases.php?case=php-notice"
    Then I should see "1" "notice" in the debug toolbar

  Scenario: I see no PHP alerts
    When I am on fixture page "/local/debugtoolbar/tests/behat/fixtures/testcases.php"
    Then I should not see PHP alerts in the debug toolbar
