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
 * JS to display modal on local_debugtoolbar plugin.
 *
 * @module      local_debugtoolbar/modal
 * @copyright   2023 Université Rennes 2 <dsi-contact@univ-rennes2.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/notification', 'core/modal_factory'], function($, Notification, ModalFactory) {
    return {
        initialize: function() {
            $('table.cachesused:eq(0)').attr('id', 'local-debugtoolbar-cache');
            $('table.cachesused:eq(1)').attr('id', 'local-debugtoolbar-sessions');

            $('.local-debugtoolbar-modal').on('click', function(e) {
                ModalFactory.create({
                    type: ModalFactory.types.ALERT,
                    title: $(e.currentTarget).text(),
                    body: $($(e.currentTarget).attr('data-targetid')),
                    large: true
                })
                .then(async function(modal) {
                    modal.show();

                    return modal;
                }).catch(Notification.exception);
            });
        }
    };
});
