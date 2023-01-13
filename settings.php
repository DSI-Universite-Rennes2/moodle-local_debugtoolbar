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
 * Add page to admin menu.
 *
 * @package   local_debugtoolbar
 * @copyright 2023 Université Rennes 2 <dsi-contact@univ-rennes2.fr>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_debugtoolbar', get_string('pluginname', 'local_debugtoolbar'));
    $ADMIN->add('localplugins', $settings);

    // Add warnings about usage.
    $content = html_writer::tag('div', get_string('usage_warning', 'local_debugtoolbar'), array('class' => 'alert alert-warning'));
    $settings->add(new admin_setting_heading('local_debugtoolbar/header', get_string('settings'), $content));

    // Add a checkbox to enable/disable module.
    $settings->add(new admin_setting_configcheckbox('local_debugtoolbar/enable',
            get_string('enable_debugtoolbar', 'local_debugtoolbar'), '', 0));

    // Add a checkbox to enable/disable error handler.
    $settings->add(new admin_setting_configcheckbox('local_debugtoolbar/enable_error_handler',
            get_string('enable_error_handler', 'local_debugtoolbar'),
            get_string('enable_error_handler_description', 'local_debugtoolbar'), 0));
}
