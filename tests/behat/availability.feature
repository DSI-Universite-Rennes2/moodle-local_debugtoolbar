@local @local_debugtoolbar
Feature: Enable and disable debug toolbar
  As an admin,
  I should be able to enable or disable debug toolbar

  Background:
    Given I log in as "admin"
    And the following config values are set as admin:
      | enable               | 1 | local_debugtoolbar |
      | enable_error_handler | 1 | local_debugtoolbar |

  Scenario: I enable the debug toolbar
    When I am on fixture page "/local/debugtoolbar/tests/behat/fixtures/testcases.php"
    Then I should see the debug toolbar

  Scenario: I disable the debug toolbar
    When I visit "/local/debugtoolbar/tests/behat/fixtures/testcases.php?case=disabled"
    Then I should not see the debug toolbar
