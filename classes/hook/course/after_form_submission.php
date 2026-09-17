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
 * Hook callback: save "Participant Photograph" setting after course form submit.
 *
 * @package   local_yucardphoto
 * @copyright 2026 ED&IT, York University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_yucardphoto\hook\course;

defined('MOODLE_INTERNAL') || die();

/**
 * Persists the yucardphoto_enabled flag into local_yucardphoto_coursesettings.
 */
class after_form_submission {

    /**
     * Save or update the course-level Photo View enabled setting.
     *
     * @param \core_course\hook\after_form_submission $hook
     */
    public static function callback(\core_course\hook\after_form_submission $hook): void {
        global $DB, $CFG;

        $data     = $hook->get_data();
        $courseid = (int)($data->id ?? 0);

        if (!$courseid) {
            return;
        }

        // The field may not be present if the section wasn't shown (new course or non-degree course).
        if (!isset($data->yucardphoto_enabled)) {
            return;
        }

        // Extra safety: only allow setting for degree courses
        require_once($CFG->dirroot . '/local/yucardphoto/lib.php');
        if (!\local_yucardphoto_is_degree_course($courseid)) {
            return;
        }

        $enabled  = (int)(bool)$data->yucardphoto_enabled;
        $existing = $DB->get_record('local_yucardphoto_coursesettings', ['courseid' => $courseid]);

        if ($existing) {
            $existing->enabled = $enabled;
            $DB->update_record('local_yucardphoto_coursesettings', $existing);
        } else {
            $DB->insert_record('local_yucardphoto_coursesettings', (object)[
                'courseid' => $courseid,
                'enabled'  => $enabled,
            ]);
        }
    }
}
