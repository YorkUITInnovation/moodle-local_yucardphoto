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
 * Library functions for local_yucardphoto.
 *
 * @package   local_yucardphoto
 * @copyright 2026 ED&IT, York University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Serve plugin files (student photos stored in the Moodle file system).
 *
 * Called by Moodle's pluginfile.php dispatcher when a URL of the form
 * /pluginfile.php/{contextid}/local_yucardphoto/photos/{itemid}/{filename}
 * is requested.
 *
 * Access is restricted to users who can view participants in the system
 * context (i.e. the same roles that can see the Photo View roster).
 *
 * @param stdClass      $course   Not used for system-context files.
 * @param stdClass      $cm       Not used.
 * @param context       $context  Must be the system context.
 * @param string        $filearea Must be 'photos'.
 * @param array         $args     [itemid, filename].
 * @param bool          $forcedownload
 * @param array         $options
 * @return void  Sends the file or throws an exception.
 */
function local_yucardphoto_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    global $DB, $USER;

    if ($context->contextlevel !== CONTEXT_SYSTEM) {
        send_file_not_found();
    }

    if ($filearea !== 'photos') {
        send_file_not_found();
    }

    // Must be logged in — guests are always denied.
    require_login();
    if (isguestuser()) {
        send_file_not_found();
    }

    // Site admins always have access.
    // For everyone else, verify they hold local/yucardphoto:viewroster in at
    // least one course context.  This matches exactly the roles defined in
    // db/access.php (manager, coursecreator, editingteacher, teacher).
    // Students do NOT have this capability and are therefore denied.
    if (!is_siteadmin()) {
        $syscontext = context_system::instance();
        // Managers may have it at system level via role override.
        $hassystem = has_capability('local/yucardphoto:viewroster', $syscontext);

        if (!$hassystem) {
            // Check whether the user has the viewroster capability in any course
            // via their role assignments.  We join role_capabilities → role_assignments
            // → context (course level only).  Students never have this capability.
            $sql = "SELECT ra.id
                      FROM {role_assignments} ra
                      JOIN {role_capabilities} rc ON rc.roleid = ra.roleid
                      JOIN {context} ctx           ON ctx.id   = ra.contextid
                     WHERE ra.userid        = :userid
                       AND rc.capability    = :cap
                       AND rc.permission    = :allow
                       AND ctx.contextlevel = :courselevel";
            $hasincourse = $DB->record_exists_sql($sql, [
                'userid'      => $USER->id,
                'cap'         => 'local/yucardphoto:viewroster',
                'allow'       => CAP_ALLOW,
                'courselevel' => CONTEXT_COURSE,
            ]);

            if (!$hasincourse) {
                send_file_not_found();
            }
        }
    }

    $itemid   = (int) array_shift($args);
    $filename = array_pop($args);
    $filepath = $args ? '/' . implode('/', $args) . '/' : '/';

    $fs   = get_file_storage();
    $file = $fs->get_file($context->id, 'local_yucardphoto', $filearea, $itemid, $filepath, $filename);

    if (!$file || $file->is_directory()) {
        send_file_not_found();
    }

    // Cache strategy: allow the browser to cache photos for up to 4 hours
    // (14400 s).  Moodle's send_stored_file sets Last-Modified and ETag based
    // on the stored file record, so an instructor who re-uploads a photo will
    // get a fresh copy on the next request after the cache window expires, or
    // immediately if the browser sends a conditional request.
    // 4 hours is a reasonable balance between performance (1000-student roster
    // only fetches changed photos) and freshness (updated photos visible same day).
    send_stored_file($file, 14400, 0, false, $options);
}

/**
 * Check whether the Photo View feature is enabled for a given course.
 *
 * @param  int  $courseid
 * @return bool
 */
function local_yucardphoto_is_enabled_for_course(int $courseid): bool {
    global $DB;
    $rec = $DB->get_record('local_yucardphoto_coursesettings', ['courseid' => $courseid]);
    return $rec && (bool)$rec->enabled;
}

/**
 * Check whether the Photo View feature is globally disabled by site admin.
 *
 * @return bool
 */
function local_yucardphoto_is_globally_disabled(): bool {
    return (bool)get_config('local_yucardphoto', 'disable_photo_view_globally');
}

/**
 * Return the roles that are allowed to see the Photo View button and roster.
 * Checked against the course context.
 *
 * The capability 'local/yucardphoto:viewroster' is assigned to:
 *   manager, coursecreator, editingteacher, teacher (non-editing instructor).
 * Site admins always pass.
 *
 * @param  context_course $context
 * @return bool
 */
function local_yucardphoto_can_view_roster(context_course $context): bool {
    return has_capability('local/yucardphoto:viewroster', $context);
}

/**
 * Store a raw image binary as a Moodle file and return the pluginfile URL.
 *
 * The file is stored in the system context under:
 *   component  : local_yucardphoto
 *   filearea   : photos
 *   itemid     : hash-derived integer (crc32 of sisid) for stable addressing
 *   filepath   : /
 *   filename   : {sisid}.jpg  (or .png depending on detected mime)
 *
 * If a file for this sisid already exists it is deleted and replaced.
 * There is always exactly ONE photo record per sisid — this function
 * enforces that invariant; callers must handle the DB upsert separately.
 *
 * @param  string $sisid       Student/SIS ID — used to name and address the file.
 * @param  string $imagedata   Raw binary image data.
 * @param  string $mime        MIME type: 'image/jpeg' or 'image/png'.
 * @param  int    $uploaded_by Moodle userid who triggered this write.
 *                             Pass -1 when called from the scheduled task.
 * @return string  Moodle pluginfile URL (with rev= cache-buster) for the stored photo.
 */
function local_yucardphoto_store_photo(string $sisid, string $imagedata, string $mime = 'image/jpeg', int $uploaded_by = -1): string {
    $syscontext = context_system::instance();
    $fs         = get_file_storage();
    $ext        = ($mime === 'image/png') ? 'png' : 'jpg';
    $filename   = $sisid . '.' . $ext;
    // Use abs(crc32) as a stable integer itemid derived from the sisid string.
    $itemid     = abs(crc32($sisid));

    // Delete any existing file for this student.
    $existing = $fs->get_file($syscontext->id, 'local_yucardphoto', 'photos', $itemid, '/', $filename);
    if ($existing) {
        $existing->delete();
    }
    // Also delete any old file with a different extension.
    $altext  = ($ext === 'jpg') ? 'png' : 'jpg';
    $altfile = $fs->get_file($syscontext->id, 'local_yucardphoto', 'photos', $itemid, '/', $sisid . '.' . $altext);
    if ($altfile) {
        $altfile->delete();
    }

    $filerecord = [
        'contextid' => $syscontext->id,
        'component' => 'local_yucardphoto',
        'filearea'  => 'photos',
        'itemid'    => $itemid,
        'filepath'  => '/',
        'filename'  => $filename,
        'mimetype'  => $mime,
        'timecreated'  => time(),
        'timemodified' => time(),
    ];

    $storedfile = $fs->create_file_from_string($filerecord, $imagedata);

    // Build and return the pluginfile URL.
    // Append rev= (timemodified) so re-uploaded photos always get a fresh URL
    // that bypasses any browser-cached copy of the previous photo.
    $url = moodle_url::make_pluginfile_url(
        $syscontext->id,
        'local_yucardphoto',
        'photos',
        $itemid,
        '/',
        $filename
    );
    $url->param('rev', $storedfile->get_timemodified());
    return $url->out(false);
}

/**
 * Check whether a course is a degree course.
 * Degree courses have a course level of 1-9 in the yorkcourseinfo table.
 * Non-degree courses (professional development, special topics, etc.) will have no
 * entry in yorkcourseinfo or a non-numeric level.
 *
 * @param  int  $courseid
 * @return bool True if the course is a degree course, false otherwise.
 */
function local_yucardphoto_is_degree_course(int $courseid): bool {
    global $DB;

    $yorkcourseinfo = $DB->get_record('yorkcourseinfo', ['moodleid' => $courseid]);

    if (!$yorkcourseinfo || empty($yorkcourseinfo->courselevel)) {
        return false;
    }

    // Degree courses have courselevel 1-9 (numeric)
    return preg_match('/^[1-9]$/', $yorkcourseinfo->courselevel) === 1;
}
