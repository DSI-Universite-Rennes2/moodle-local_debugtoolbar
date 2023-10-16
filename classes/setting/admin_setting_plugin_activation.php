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
 * Class to handle local_debugtoolbar plugin activation.
 *
 * @package    local_debugtoolbar
 * @copyright  2023 Université Rennes 2 <dsi-contact@univ-rennes2.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_debugtoolbar\setting;

use admin_setting_configcheckbox;

/**
 * Class to handle local_debugtoolbar plugin activation.
 *
 * @package    local_debugtoolbar
 * @copyright  2023 Université Rennes 2 <dsi-contact@univ-rennes2.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_setting_plugin_activation extends admin_setting_configcheckbox {
    /**
     * List of setting names that local_debugtoolbar overrides if the plugin is enabled.
     *
     * @return array
     */
    public function get_overrided_settings() {
        $settings = [];
        $settings['debug'] = DEBUG_DEVELOPER;
        $settings['debugdisplay'] = true;
        $settings['perfdebug'] = 15;

        return $settings;
    }

    /**
     * We need to overwrite some global settings if local_debugtoolbar is enabled.
     *
     * @param string $data Form data.
     *
     * @return string Empty when no errors.
     */
    public function write_setting($data) {
        global $CFG;

        if (isset($data) === true && empty($data) === false) {
            // Enable field has been checked in form.
            if (empty(get_config('local_debugtoolbar', 'enable')) === true) {
                // The plugin is not already enabled.

                $settings = $this->get_overrided_settings();
                foreach ($settings as $name => $value) {
                    // We store current global value in local_debugtoolbar scope.
                    set_config($name, $CFG->{$name}, 'local_debugtoolbar');

                    // We change global value with value required by local_debugtoolbar.
                    set_config($name, $value);
                }
            }
        } else {
            // Enable field has not been checked in form.
            if (empty(get_config('local_debugtoolbar', 'enable')) === false) {
                // The plugin is not already disabled.

                $settings = $this->get_overrided_settings();
                foreach ($settings as $name => $value) {
                    // We get stored value in local_debugtoolbar scope.
                    $oldvalue = get_config('local_debugtoolbar', $name);

                    // We restore global value.
                    set_config($name, $oldvalue);
                }
            }
        }

        return parent::write_setting($data);
    }
}
