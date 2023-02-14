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
 * Debug toolbar library code.
 *
 * @package    local_debugtoolbar
 * @copyright  2023 Université Rennes 2 <dsi-contact@univ-rennes2.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Setup error handler as soon as practical on every moodle bootstrap after config has been loaded.
 *
 * @return void
 */
function local_debugtoolbar_after_config() {
    global $PAGE;

    if (empty(get_config('local_debugtoolbar', 'enable')) === true) {
        // Return nothing if plugin has been disabled.
        return;
    }

    $PAGE->add_body_class('local-debugtoolbar-enabled');

    $_SESSION['local_debugtoolbar'] = array('errors' => [], 'warnings' => [], 'notices' => [], 'deprecated' => []);

    if (empty(get_config('local_debugtoolbar', 'enable_error_handler')) === false) {
        set_error_handler('local_debugtoolbar_error_handler');
    }
}

/**
 * Function to handle and catch almost all PHP errors.
 *
 * @param int    $errno      The level of the error raised, as an integer.
 * @param string $errstr     The error message, as a string.
 * @param string $errfile    The filename that the error was raised in, as a string.
 * @param int    $errline    The line number where the error was raised, as an integer.
 * @param array  $errcontext Unused parameter.
 *
 * @return bool
 */
function local_debugtoolbar_error_handler($errno, $errstr, $errfile, $errline, $errcontext = null) {
    switch ($errno) {
        case E_RECOVERABLE_ERROR:
        case E_STRICT:
        case E_USER_ERROR:
            $type = 'errors';
            break;
        case E_WARNING:
        case E_USER_WARNING:
            $type = 'warnings';
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
            $type = 'notices';
            break;
        case E_DEPRECATED:
        case E_USER_DEPRECATED:
            $type = 'deprecated';
            break;
        default:
            return false;
    }

    switch ($errno) {
        case E_RECOVERABLE_ERROR:
        case E_STRICT:
        case E_WARNING:
        case E_NOTICE:
        case E_DEPRECATED:
            $errstr = htmlspecialchars($errstr);
            $format = 'PHP %s: %s in %s on line %s';
            $_SESSION['local_debugtoolbar'][$type][] = sprintf($format, ucfirst($type), $errstr, $errfile, $errline);
            break;
        default:
            $_SESSION['local_debugtoolbar'][$type][] = sprintf('MOODLE %s: %s', ucfirst($type), $errstr);
    }

    // Disable default PHP handler.
    return true;
}

/**
 * Gather performance data and return HTML string to render the debug toolbar.
 *
 * @see get_performance_info() dans lib/moodlelib.php
 *
 * @return string
 */
function local_debugtoolbar_before_footer() {
    global $CFG, $OUTPUT, $PAGE, $PERF, $USER;

    if (empty(get_config('local_debugtoolbar', 'enable')) === true) {
        // Return nothing if plugin has been disabled.
        return '';
    }

    require($CFG->dirroot.'/version.php');

    $performance = get_performance_info();

    if (function_exists('posix_times')) {
        $format = 'Ticks: %s user: %s sys: %s cuser: %s csys: %s';
        $ticks = sprintf($format, $performance['ticks'], $performance['utime'], $performance['stime'],
            $performance['cutime'], $performance['cstime']);
    }

    $data = new stdClass();
    $data->records = array();

    // Moodle.
    $i = 0;
    $label = 'Moodle';
    $maturitylabel = get_string('maturity' . $maturity, 'admin');
    $data->records[$i] = (object) ['title' => $label, 'fa' => 'graduation-cap', 'items' => []];
    $data->records[$i]->items[] = (object) ['title' => get_string('version_X', 'local_debugtoolbar', $release)];
    $data->records[$i]->items[] = (object) ['title' => get_string('maturity_X', 'local_debugtoolbar', $maturitylabel)];
    $data->records[$i]->items[] = (object) ['title' => get_string('php_X', 'local_debugtoolbar', phpversion())];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('http_response_code_X', 'local_debugtoolbar', http_response_code())
    ];

    switch ($maturity) {
        case MATURITY_STABLE:
            break;
        case MATURITY_ALPHA:
            $data->records[$i]->items[1]->style = 'bg-danger';
            $data->records[$i]->style = 'btn-danger';
            break;
        case MATURITY_BETA:
        default:
            $data->records[$i]->items[1]->style = 'bg-warning';
            $data->records[$i]->style = 'btn-warning';
    }

    $updateschecker = \core\update\checker::instance();
    if ($updateschecker->get_last_timefetched() < (time() + DAYSECS)) {
        if ($updateschecker->enabled()) {
            $updateschecker->fetch();
        }
    }

    $parameters = array('minmaturity' => $CFG->updateminmaturity, 'notifybuilds' => $CFG->updatenotifybuilds);
    if (empty($updateschecker->get_update_info('core', $parameters)) === false) {
        $data->records[$i]->style = 'btn-info';
        $item = (object) ['title' => get_string('updateavailable', 'admin'), 'style' => 'bg-info'];
        array_unshift($data->records[$i]->items, $item);
    }

    // PHP alerts.
    if (empty(get_config('local_debugtoolbar', 'enable_error_handler')) === false) {
        $i++;
        $data->alerts = array();
        $label = get_string('no_alerts', 'local_debugtoolbar');
        $data->records[$i] = (object) ['title' => $label, 'fa' => 'exclamation-circle', 'items' => []];
        foreach (['errors' , 'warnings', 'notices', 'deprecated'] as $type) {
            $style = '';
            $count = count($_SESSION['local_debugtoolbar'][$type]);
            $label = get_string(sprintf('%s_X', $type), 'local_debugtoolbar', $count);

            if ($count > 0) {
                $style = 'bg-danger';

                if (isset($data->records[$i]->style) === false) {
                    $data->records[$i]->title = $label;
                    $data->records[$i]->style = 'btn-danger';
                }

                $alerts = (object) ['type' => $type, 'items' => []];
                foreach ($_SESSION['local_debugtoolbar'][$type] as $alert) {
                    $alerts->items[] = $alert;
                }
                $data->alerts[] = $alerts;

                $modal = sprintf('#local-debugtoolbar-alerts-%s', $type);
                $data->records[$i]->items[] = (object) ['title' => $label, 'style' => $style, 'modal' => $modal];
            } else {
                $data->records[$i]->items[] = (object) ['title' => $label, 'style' => $style];
            }
        }
    }

    // Time speed.
    $i++;
    $label = get_string('X_secs', 'local_debugtoolbar', $performance['realtime']);
    $data->records[$i] = (object) ['title' => $label, 'fa' => 'clock-o', 'items' => []];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('total_time_X', 'local_debugtoolbar', $performance['realtime'])
    ];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('load_average_X', 'local_debugtoolbar', $performance['serverload'])
    ];
    if (isset($ticks)) {
        $data->records[$i]->items[] = (object) ['title' => $ticks];
    }
    if ($performance['realtime'] > get_config('local_debugtoolbar', 'realtime_critical_threshold')) {
        $data->records[$i]->style = 'btn-danger';
        $data->records[$i]->items[0]->style = 'bg-danger';
    } else if ($performance['realtime'] > get_config('local_debugtoolbar', 'realtime_warning_threshold')) {
        $data->records[$i]->style = 'btn-warning';
        $data->records[$i]->items[0]->style = 'bg-warning';
    }

    // Memory usage.
    $i++;
    $label = display_size($performance['memory_total']);
    $data->records[$i] = (object) ['title' => $label, 'fa' => 'microchip', 'items' => []];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('ram_X', 'local_debugtoolbar', display_size($performance['memory_total']))
    ];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('ram_growth_X', 'local_debugtoolbar', display_size($performance['memory_growth']))
    ];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('ram_peak_X', 'local_debugtoolbar', display_size($performance['memory_peak']))
    ];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('extra_memory_limit_X', 'local_debugtoolbar', $CFG->extramemorylimit)
    ];

    // Database.
    $i++;
    list($countread, $countwrite) = explode('/', $performance['dbqueries']);
    $parameters = (object) ['queries' => ($countread + $countwrite), 'time' => $performance['dbtime']];
    $label = get_string('X_queries_in_Y_secs', 'local_debugtoolbar', $parameters);
    $data->records[$i] = (object) ['title' => $label, 'fa' => 'database', 'items' => []];
    $data->records[$i]->items[] = (object) ['title' => get_string('db_reads_X', 'local_debugtoolbar', $countread)];
    $data->records[$i]->items[] = (object) ['title' => get_string('db_writes_X', 'local_debugtoolbar', $countwrite)];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('db_queries_time_X', 'local_debugtoolbar', $performance['dbtime'])
    ];

    if ($countread >= get_config('local_debugtoolbar', 'dbqueries_critical_threshold')) {
        $data->records[$i]->style = 'btn-danger';
        $data->records[$i]->items[0]->style = 'bg-danger';
    } else if ($countread >= get_config('local_debugtoolbar', 'dbqueries_warning_threshold')) {
        $data->records[$i]->style = 'btn-warning';
        $data->records[$i]->items[0]->style = 'bg-warning';
    }

    // Cache.
    $i++;
    list($cachehits, $cachemisses, $cachesets) = explode('/', $performance['cachesused']);
    foreach (array('cachehits', 'cachemisses', 'cachesets') as $variable) {
        ${$variable} = trim(${$variable});
    }
    $cachecalls = $cachehits + $cachemisses;
    if ($cachecalls === 0) {
        $cacheratio = '100%';
    } else {
        $cacheratio = sprintf('%.2f%%', $cachehits / $cachecalls * 100);
    }
    $label = get_string('cache_ratio_X', 'local_debugtoolbar', $cacheratio);
    $data->records[$i] = (object) ['title' => $label, 'fa' => 'tasks', 'items' => []];
    $data->records[$i]->items[] = (object) ['title' => get_string('cache_hits_X', 'local_debugtoolbar', $cachehits)];
    $data->records[$i]->items[] = (object) ['title' => get_string('cache_misses_X', 'local_debugtoolbar', $cachemisses)];
    $data->records[$i]->items[] = (object) ['title' => get_string('cache_sets_X', 'local_debugtoolbar', $cachesets)];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('included_X_files', 'local_debugtoolbar', $performance['includecount'])
    ];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('more...', 'local_debugtoolbar'), 'modal' => '#local-debugtoolbar-cache'
    ];

    // String.
    $i++;
    $label = get_string('filters_and_strings', 'local_debugtoolbar');
    $data->records[$i] = (object) ['title' => $label, 'fa' => 'book', 'items' => []];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('contexts_for_which_filters_were_loaded_X', 'local_debugtoolbar', $performance['contextswithfilters'])
    ];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('filters_created_X', 'local_debugtoolbar', $performance['filterscreated'])
    ];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('pieces_of_content_filtered_X', 'local_debugtoolbar', $performance['textsfiltered'])
    ];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('strings_filtered_X', 'local_debugtoolbar', $performance['stringsfiltered'])
    ];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('get_string_calls_X', 'local_debugtoolbar', $performance['langcountgetstring'])
    ];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('included_X_files', 'local_debugtoolbar', $performance['includecount'])
    ];

    // Sessions.
    $i++;
    $label = get_string('sessions', 'local_debugtoolbar');
    $data->records[$i] = (object) ['title' => $label, 'fa' => 'fa-cloud-upload', 'items' => []];
    $si = \core\session\manager::get_performance_info();
    $data->records[$i]->items[] = (object) ['title' => $si['txt']];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('session_wait_X', 'local_debugtoolbar', number_format($PERF->sessionlock['wait'], 3))
    ];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('more...', 'local_debugtoolbar'), 'modal' => '#local-debugtoolbar-sessions'
    ];

    // User.
    $i++;
    if (empty($USER->id) === false) {
        $isadmin = is_siteadmin() ? get_string('yes') : get_string('no');
        $label = sprintf('%s (%s)', fullname($USER), get_string('site_admin_X', 'local_debugtoolbar', $isadmin));
    } else {
        $label = get_string('guest');
    }
    $data->records[$i] = (object) ['title' => $label, 'fa' => 'user', 'items' => []];
    $data->records[$i]->items[] = (object) ['title' => get_string('general_type_X', 'local_debugtoolbar', $PAGE->pagelayout)];
    $data->records[$i]->items[] = (object) ['title' => get_string('contextid_X', 'local_debugtoolbar', $PAGE->context->id)];
    $data->records[$i]->items[] = (object) [
        'title' => get_string('context_X', 'local_debugtoolbar', $PAGE->context->get_context_name())
    ];
    $data->records[$i]->items[] = (object) ['title' => get_string('page_type_X', 'local_debugtoolbar', $PAGE->pagetype)];
    if ($PAGE->subpage) {
        $data->records[$i]->items[] = (object) ['title' => get_string('subpage_X', 'local_debugtoolbar', $PAGE->subpage)];
    }

    // Display content.
    $PAGE->requires->js_call_amd('local_debugtoolbar/modal', 'initialize');

    return $OUTPUT->render_from_template('local_debugtoolbar/toolbar', $data);
}
