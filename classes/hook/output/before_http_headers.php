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
 * Hook callback: inject the "Photo View" button on user/index.php.
 *
 * Uses the before_http_headers hook to inject an AMD module that appends
 * the button into the participants page tertiary nav after the DOM is ready.
 *
 * @package   local_yucardphoto
 * @copyright 2026 ED&IT, York University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_yucardphoto\hook\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Before HTTP headers hook — injects the Photo View button AMD module.
 */
class before_http_headers {

    /**
     * Callback invoked by Moodle's hook dispatcher before headers are sent.
     *
     * Only acts on user/index.php (participants page) when the roster is
     * available for the course and the current user is allowed to view it.
     *
     * @param \core\hook\output\before_http_headers $hook
     */
    public static function callback(\core\hook\output\before_http_headers $hook): void {
        global $PAGE, $CFG;

        require_once($CFG->dirroot . '/local/yucardphoto/lib.php');

        // Detect the participants page by URL path — $PAGE->pagetype is not
        // reliably set this early in the request lifecycle (before headers).
        $url = $PAGE->url;
        if (!$url) {
            return;
        }
        $path = $url->get_path();
        // Match /user/index.php (with or without a subfolder prefix).
        if (substr($path, -strlen('/user/index.php')) !== '/user/index.php') {
            return;
        }

        // Resolve the course id — prefer $PAGE->course (set by require_login)
        // over the $COURSE global which may not be populated yet at this hook.
        $courseid = 0;
        if (!empty($PAGE->course->id) && $PAGE->course->id != SITEID) {
            $courseid = (int)$PAGE->course->id;
        } else {
            // Fall back to the 'id' URL parameter.
            $courseid = (int)($url->param('id') ?: 0);
        }

        if (!$courseid || $courseid == SITEID) {
            return;
        }

        $context = \context_course::instance($courseid, IGNORE_MISSING);
        if (!$context) {
            return;
        }

        // Only show the button when the roster is available for this course.
        if (!\local_yucardphoto_can_show_roster($courseid, $context)) {
            return;
        }

        // Queue the AMD module — it will append the button once the DOM is ready.
        $photourl = new \moodle_url('/local/yucardphoto/participants.php', ['id' => $courseid]);

        $PAGE->requires->js_call_amd(
            'local_yucardphoto/photoview_button',
            'init',
            [['url' => $photourl->out(false), 'label' => get_string('viewphoto', 'local_yucardphoto')]]
        );
    }
}
