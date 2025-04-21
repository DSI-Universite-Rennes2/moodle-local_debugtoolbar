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

    $errors = [E_RECOVERABLE_ERROR, E_USER_ERROR];
    if (PHP_MAJOR_VERSION <= 8 && PHP_MINOR_VERSION <= 3) {
        // E_STRICT constant is deprecated since PHP 8.4.
        $errors[] = E_STRICT;
    }

    $warnings = [E_WARNING, E_USER_WARNING];
    $notices = [E_NOTICE, E_USER_NOTICE];
    $deprecated = [E_DEPRECATED, E_USER_DEPRECATED];

    if (in_array($errno, $errors, $strict = true) === true) {
        $type = 'errors';
    } else if (in_array($errno, $warnings, $strict = true) === true) {
        $type = 'warnings';
    } else if (in_array($errno, $notices, $strict = true) === true) {
        $type = 'notices';
    } else if (in_array($errno, $deprecated, $strict = true) === true) {
        $type = 'deprecated';
    } else {
        // Unkown error type.
        return false;
    }

    $errortypes = [E_RECOVERABLE_ERROR, E_WARNING, E_NOTICE, E_DEPRECATED];
    if (PHP_MAJOR_VERSION <= 8 && PHP_MINOR_VERSION <= 3) {
        // E_STRICT constant is deprecated since PHP 8.4.
        $errortypes[] = E_STRICT;
    }

    if (in_array($errno, $errortypes, $strict = true) === true) {
        $errstr = htmlspecialchars($errstr);
        $format = 'PHP %s: %s in %s on line %s';
        $PERF->local_debugtoolbar[$type][] = sprintf($format, ucfirst($type), $errstr, $errfile, $errline);
    } else {
        $PERF->local_debugtoolbar[$type][] = sprintf('MOODLE %s: %s', ucfirst($type), $errstr);
    }

    // Disable default PHP handler.
    return true;
}
