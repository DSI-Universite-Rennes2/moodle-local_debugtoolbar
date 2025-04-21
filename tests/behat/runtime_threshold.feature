@local @local_debugtoolbar
Feature: Monitor runtime
  As an admin,
  I should be able to see if the page is slow.

  Background:
    Given I log in as "admin"
    And the following config values are set as admin:
      | enable               | 1 | local_debugtoolbar |
      | enable_error_handler | 1 | local_debugtoolbar |

  Scenario: I see a warning alert about runtime
    When I visit "/local/debugtoolbar/tests/behat/fixtures/testcases.php?case=runtime-warning"
    Then I should see a warning alert about runtime in the debug toolbar

  Scenario: I see a danger alert about runtime
    When I visit "/local/debugtoolbar/tests/behat/fixtures/testcases.php?case=runtime-critical"
    Then I should see a danger alert about runtime in the debug toolbar

  Scenario: I see no alerts about database queries
    When I am on fixture page "/local/debugtoolbar/tests/behat/fixtures/testcases.php"
    Then I should not see runtime alerts in the debug toolbar
