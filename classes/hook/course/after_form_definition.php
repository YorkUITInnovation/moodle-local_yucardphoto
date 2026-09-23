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
 * Hook callback: add "Participant Photograph" section to course edit form.
 *
 * @package   local_yucardphoto
 * @copyright 2026 ED&IT, York University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_yucardphoto\hook\course;

defined('MOODLE_INTERNAL') || die();

/**
 * Extends the course edit form with the Participant Photograph toggle.
 */
class after_form_definition {

    /**
     * Add the "Participant Photograph" header and yes/no select to the form.
     *
     * @param \core_course\hook\after_form_definition $hook
     */
    public static function callback(\core_course\hook\after_form_definition $hook): void {
        global $CFG;
        
        $mform    = $hook->mform;
        $wrapper  = $hook->formwrapper;
        $course   = $wrapper->get_course();

        // Only show for existing courses (no sense enabling it before the
        // course exists and has participants).
        if (empty($course->id)) {
            return;
        }

        // Only show for degree courses. Professional development and other
        // non-degree courses are not eligible for Photo View.
        require_once($CFG->dirroot . '/local/yucardphoto/lib.php');
        if (!\local_yucardphoto_is_degree_course((int)$course->id)) {
            return;
        }

        $isgloballydisabled = \local_yucardphoto_is_globally_disabled();

        // Section header.
        $mform->addElement(
            'header',
            'yucardphoto_header',
            get_string('coursesettings_heading', 'local_yucardphoto')
        );
        // Help text displayed as a static element below the header.
        $mform->addElement(
            'static',
            'yucardphoto_header_desc',
            '',
            get_string('coursesettings_heading_desc', 'local_yucardphoto')
        );

        // Yes / No select.
        $options = [
            0 => get_string('enable_photo_view_no',  'local_yucardphoto'),
            1 => get_string('enable_photo_view_yes', 'local_yucardphoto'),
        ];
        $mform->addElement(
            'select',
            'yucardphoto_enabled',
            get_string('enable_photo_view', 'local_yucardphoto'),
            $options
        );
        $mform->addHelpButton('yucardphoto_enabled', 'enable_photo_view', 'local_yucardphoto');
        $mform->setType('yucardphoto_enabled', PARAM_INT);

        if ($isgloballydisabled) {
            $mform->freeze('yucardphoto_enabled');
            $mform->addElement(
                'static',
                'yucardphoto_globally_disabled_notice',
                '',
                get_string('enable_photo_view_globally_disabled_notice', 'local_yucardphoto')
            );
        }

        // Pre-populate with the current saved value.
        global $DB;
        $rec = $DB->get_record('local_yucardphoto_coursesettings', ['courseid' => (int)$course->id]);
        $mform->setDefault('yucardphoto_enabled', $rec ? (int)$rec->enabled : 0);
    }
}
