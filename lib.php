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
    global $PAGE, $PERF;

    if (empty(get_config('local_debugtoolbar', 'enable')) === true) {
        // Return nothing if plugin has been disabled.
        return;
    }

    $PAGE->add_body_class('local-debugtoolbar-enabled');

    $PERF->local_debugtoolbar = ['errors' => [], 'warnings' => [], 'notices' => [], 'deprecated' => []];

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
    global $PERF;

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
            $PERF->local_debugtoolbar[$type][] = sprintf($format, ucfirst($type), $errstr, $errfile, $errline);
            break;
        default:
            $PERF->local_debugtoolbar[$type][] = sprintf('MOODLE %s: %s', ucfirst($type), $errstr);
    }

    // Disable default PHP handler.
    return true;
}
