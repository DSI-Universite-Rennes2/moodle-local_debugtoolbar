<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;

/**
 * Steps definitions related with the debug toolbar.
 *
 * @package    local_debugtoolbar
 * @copyright  2025 Université Rennes 2
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_debugtoolbar extends behat_base {
    /**
     * Check if the debug toolbar is enabled.
     *
     * @Then /^I should see the debug toolbar$/
     *
     * @return void
     */
    public function i_should_see_the_debug_toolbar() {
        $xpath = '//*[@id="local-debugtoolbar"]';

        $this->find('xpath', $xpath);
    }

    /**
     * Check if the debug toolbar is disabled.
     *
     * @Then /^I should not see the debug toolbar$/
     *
     * @return void
     */
    public function i_should_not_see_the_debug_toolbar() {
        $xpath = '//*[@id="local-debugtoolbar"]';
        try {
            $this->find('xpath', $xpath);
        } catch (ElementNotFoundException $e) {
            // All ok.
            return;
        }

        throw new ExpectationException('The following element "'.$xpath.'" should not exist', $this->getSession());
    }

    /**
     * Check if there are some PHP alerts.
     *
     * @Then /^I should see "([0-9]+)" "(notice|deprecation|warning|error)s?" in the debug toolbar$/
     *
     * @param string $count      Number of PHP errors expected.
     * @param string $errorlevel PHP error level: notice, deprecation, warning or error.
     *
     * @return void
     */
    public function i_should_see_some_php_alerts(string $count, string $errorlevel) {
        $label = '';
        switch ($errorlevel) {
            case 'notice':
                $label = 'Notices: '.$count;
                break;
            case 'deprecation':
                $label = 'Deprecated: '.$count;
                break;
            case 'warning':
                $label = 'Warnings: '.$count;
                break;
            case 'error':
                $label = 'Errors: '.$count;
                break;
        }

        $text = sprintf('contains(normalize-space(.), "%s")', $label);
        $xpath = sprintf('//*[@id="local-debugtoolbar-fa-exclamation-circle-label"][%s]', $text);

        $this->find('xpath', $xpath);
    }

    /**
     * Check there are no PHP alerts.
     *
     * @Then /^I should not see PHP alerts in the debug toolbar$/
     *
     * @return void
     */
    public function i_should_not_see_php_alerts() {
        $text = 'contains(normalize-space(.), "No alerts")';
        $xpath = sprintf('//*[@id="local-debugtoolbar-fa-exclamation-circle-label"][%s]', $text);

        $this->find('xpath', $xpath);
    }

    /**
     * Check if there are some database queries alerts.
     *
     * @Then /^I should see a (warning|danger) alert about database queries in the debug toolbar$/
     *
     * @param string $errorlevel Alert error level: warning or danger.
     *
     * @return void
     */
    public function i_should_see_an_alert_about_database_queries(string $errorlevel) {
        $xpath = sprintf('//*[@id="local-debugtoolbar-fa-database-label"]//parent::button[contains(@class, "btn-%s")]',
            $errorlevel);
        $this->find('xpath', $xpath);
    }

    /**
     * Check if there are some database queries alerts.
     *
     * @Then /^I should not see database queries alerts in the debug toolbar$/
     *
     * @return void
     */
    public function i_should_not_see_an_alert_about_database_queries() {
        $xpath = '//*[@id="local-debugtoolbar-fa-database-label"]//parent::button[contains(@class, "bg-dark")]';
        $this->find('xpath', $xpath);
    }

    /**
     * Check if there are some runtime alerts.
     *
     * @Then /^I should see a (warning|danger) alert about runtime in the debug toolbar$/
     *
     * @param string $errorlevel Alert error level: warning or danger.
     *
     * @return void
     */
    public function i_should_see_an_alert_about_runtime(string $errorlevel) {
        $xpath = sprintf('//*[@id="local-debugtoolbar-fa-clock-o-label"]//parent::button[contains(@class, "btn-%s")]', $errorlevel);
        $this->find('xpath', $xpath);
    }

    /**
     * Check if there are some runtime alerts.
     *
     * @Then /^I should not see runtime alerts in the debug toolbar$/
     *
     * @return void
     */
    public function i_should_not_see_an_alert_about_runtime() {
        $xpath = '//*[@id="local-debugtoolbar-fa-clock-o-label"]//parent::button[contains(@class, "bg-dark")]';
        $this->find('xpath', $xpath);
    }
}
