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

define(['core/notification', 'core/modal_factory'], function(Notification, ModalFactory) {
    return {
        initialize: function() {
            let cacheTable = document.querySelectorAll('table.cachesused');
            if (cacheTable[0]) {
                cacheTable[0].setAttribute('id', 'local-debugtoolbar-cache');
            }

            if (cacheTable[1]) {
                cacheTable[1].setAttribute('id', 'local-debugtoolbar-sessions');
            }

            let modals = document.getElementsByClassName('local-debugtoolbar-modal');
            modals.forEach(function(modal) {
                modal.addEventListener('click', function(e) {
                    let targetid = e.currentTarget.getAttribute('data-targetid');
                    var targetElement = document.getElementById(targetid);

                    ModalFactory.create({
                        type: ModalFactory.types.ALERT,
                        title: e.currentTarget.textContent,
                        body: targetElement,
                        large: true
                    })
                    .then(async function(modal) {
                        modal.show();

                        return modal;
                    }).catch(Notification.exception);
                });
            });
        }
    };
});
