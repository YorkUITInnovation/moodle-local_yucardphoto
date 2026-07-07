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
 * AMD module: interactive controls for the YU Card Photo Roster page.
 *
 * Name-initial filtering is handled by Moodle core's initials selector
 * component (`core_course/actionbar/initials`).
 *
 * This module binds click-to-enlarge behavior for roster photos.
 *
 * Note on DB usage: the PHP page fetches enrolled users + photo records once
 * per page load using two queries (get_enrolled_users + one IN query). All
 * search/sort/pagination is then done in PHP memory — no extra queries per
 * filter interaction. Each form submit costs exactly the same two queries
 * regardless of search term or sort order.
 *
 * @module     local_yucardphoto/participants
 * @copyright  2026 ED&IT, York University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Initialise the participants page controls.
 *
 * @param {Object} config
 * @param {number} config.debounce Unused, kept for API compatibility.
 */
export const init = (config) => {
    const cfg = Object.assign({debounce: 600}, config || {});
    // Retained for API compatibility.
    void cfg;

    const modal = document.getElementById('ycp-photo-modal');
    if (!modal) {
        return;
    }

    const modalImage = modal.querySelector('[data-region="ycp-modal-image"]');
    const modalTitle = modal.querySelector('[data-region="ycp-modal-title"]');
    const triggers = document.querySelectorAll('[data-action="ycp-enlarge-photo"]');

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', () => {
            if (!modalImage || !modalTitle) {
                return;
            }

            const src = trigger.getAttribute('data-photo-src') || '';
            const alt = trigger.getAttribute('data-photo-alt') || '';
            const name = trigger.getAttribute('data-photo-name') || '';

            modalImage.setAttribute('src', src);
            modalImage.setAttribute('alt', alt);
            modalTitle.textContent = name;
        });
    });
};
