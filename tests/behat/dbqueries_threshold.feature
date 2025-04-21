@local @local_debugtoolbar
Feature: Monitor database queries
  As an admin,
  I should be able to see if there are too many database queries on the current page.

  Background:
    Given I log in as "admin"
    And the following config values are set as admin:
      | enable               | 1 | local_debugtoolbar |
      | enable_error_handler | 1 | local_debugtoolbar |

  Scenario: I see a warning alert about database queries
    When I visit "/local/debugtoolbar/tests/behat/fixtures/testcases.php?case=dbqueries-warning"
    Then I should see a warning alert about database queries in the debug toolbar

  Scenario: I see a danger alert about database queries
    When I visit "/local/debugtoolbar/tests/behat/fixtures/testcases.php?case=dbqueries-critical"
    Then I should see a danger alert about database queries in the debug toolbar

  Scenario: I see no alerts about database queries
    When I am on fixture page "/local/debugtoolbar/tests/behat/fixtures/testcases.php"
    Then I should not see database queries alerts in the debug toolbar
