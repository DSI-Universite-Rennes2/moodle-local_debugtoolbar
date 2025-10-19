@local @local_debugtoolbar
Feature: Close/remove the debug toolbar
  As an admin,
  I should be able to close the debug toolbar.

  Background:
    Given I log in as "admin"
    And the following config values are set as admin:
      | enable               | 1 | local_debugtoolbar |
      | enable_error_handler | 1 | local_debugtoolbar |

  Scenario: I can close the debug toolbar
    When I am on fixture page "/local/debugtoolbar/tests/behat/fixtures/testcases.php"
    And I click on "Close" "link"
    Then I should not see the debug toolbar
