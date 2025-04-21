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

/**
 * Fixture page for the debug toolbar.
 *
 * @package    local_debugtoolbar
 * @copyright  2025 Université Rennes 2
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../../../config.php');

defined('BEHAT_SITE_RUNNING') || die();

$case = optional_param('case', 'none', PARAM_ALPHAEXT);

$title = 'Test page for Debug Toolbar';
$url = '/local/debugtoolbar/tests/behat/fixtures/testcases.php';
$PAGE->set_url($url, ['case' => $case]);

require_login();

$PAGE->set_context(core\context\system::instance());
$PAGE->set_title($title);

if (empty(get_config('local_debugtoolbar', 'enable')) === true) {
    // Always ensure that the debug toolbar is always enabled to avoir PHP warning messages.
    set_config('enable', 1, 'local_debugtoolbar');
    $redirect = new moodle_url($url, ['case' => $case]);
    redirect($redirect);
}

set_config('enable', 1, 'local_debugtoolbar');
set_config('enable_error_handler', 1, 'local_debugtoolbar');
set_config('realtime_warning_threshold', 1000, 'local_debugtoolbar');
set_config('realtime_critical_threshold', 2000, 'local_debugtoolbar');
set_config('dbqueries_warning_threshold', 1000, 'local_debugtoolbar');
set_config('dbqueries_critical_threshold', 2000, 'local_debugtoolbar');

$dbqueries = 0;
switch ($case) {
    case 'php-notice':
        trigger_error('This is a PHP notice.',  E_USER_NOTICE);
        break;
    case 'php-deprecated':
        trigger_error('This is a PHP deprecation.',  E_USER_DEPRECATED);
        break;
    case 'php-warning':
        trigger_error('This is a PHP warning.',  E_USER_WARNING);
        break;
    case 'php-error':
        trigger_error('This is a PHP error.',  E_USER_ERROR);
        break;
    case 'runtime-warning':
        set_config('realtime_warning_threshold', 1, 'local_debugtoolbar');
        sleep(1);
        break;
    case 'runtime-critical':
        set_config('realtime_warning_threshold', 0.2, 'local_debugtoolbar');
        set_config('realtime_critical_threshold', 1, 'local_debugtoolbar');
        sleep(1);
        break;
    case 'dbqueries-warning':
        set_config('dbqueries_warning_threshold', 100, 'local_debugtoolbar');
        $dbqueries = 100;
        break;
    case 'dbqueries-critical':
        set_config('dbqueries_warning_threshold', 1, 'local_debugtoolbar');
        set_config('dbqueries_critical_threshold', 100, 'local_debugtoolbar');
        $dbqueries = 100;
        break;
    case 'disabled':
        set_config('enable', 0, 'local_debugtoolbar');
        break;
    case 'none':
    default:
        $case = 'none';
}

// Generate many DB queries.
for ($i = 0; $i < $dbqueries; $i++) {
    $record = $DB->get_record('user', ['id' => $i]);
}

// Generate the list of available tests.
$items = [];
$items[] = html_writer::link(new moodle_url($url, ['case' => 'php-notice']), 'PHP notice');
$items[] = html_writer::link(new moodle_url($url, ['case' => 'php-deprecated']), 'PHP deprecation');
$items[] = html_writer::link(new moodle_url($url, ['case' => 'php-warning']), 'PHP warning');
$items[] = html_writer::link(new moodle_url($url, ['case' => 'php-error']), 'PHP error');
$items[] = html_writer::link(new moodle_url($url, ['case' => 'runtime-warning']), 'Runtime with warning');
$items[] = html_writer::link(new moodle_url($url, ['case' => 'runtime-critical']), 'Runtime with critical');
$items[] = html_writer::link(new moodle_url($url, ['case' => 'dbqueries-warning']), 'Database queries with warning');
$items[] = html_writer::link(new moodle_url($url, ['case' => 'dbqueries-critical']), 'Database queries with critical');
$items[] = html_writer::link(new moodle_url($url, ['case' => 'none']), 'Without errors');
$items[] = html_writer::link(new moodle_url($url, ['case' => 'disabled']), 'Debug Toolbar disabled');

// Show page.
echo $OUTPUT->header();
echo $OUTPUT->heading($title);
echo html_writer::div('List of available tests:', 'mt-5');
echo html_writer::alist($items, $attributes = null, $tag = 'ul');
echo $OUTPUT->footer();
