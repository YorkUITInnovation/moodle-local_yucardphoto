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
 * Language strings for local_yucardphoto.
 *
 * @package   local_yucardphoto
 * @copyright 2026 ED&IT, York University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin name.
$string['pluginname'] = 'YU Card Photo Roster';

// Task.
$string['task_import_yucard_photos'] = 'Import YU Card photos from external database';

// Course setting.
$string['coursesettings_heading']         = 'Photo Roster';
$string['coursesettings_heading_desc']    = 'Controls whether the Photo Roster button is shown on the Participants page for this course.';
$string['enable_photo_view']              = 'Enable Photo Roster';
$string['enable_photo_view_desc']         = 'When enabled, authorised roles (instructors, managers, etc.) will see a "Photo Roster" button on the Participants page that opens the YU Card Photo Roster for this course.';
$string['enable_photo_view_yes']          = 'Yes';
$string['enable_photo_view_no']           = 'No';
$string['enable_photo_view_help']        = 'Yes - Show Photo Roster button, No - Hide Photo Roster button.';

// Participants page.
$string['photoview']           = 'Photo Roster';
$string['photoviewtitle']      = 'Photo Roster - {$a}';
$string['viewphoto']           = 'Photo Roster';
$string['backtoparticipants'] = 'Participants List';
$string['search']             = 'Search';
$string['searchplaceholder']  = 'Search users or Student ID';
$string['sortby']             = 'Sort by';
$string['sortbyfirstname']    = 'First name';
$string['sortbylastname']     = 'Last name';
$string['sortbyemail']        = 'Email';
$string['sortbysisid']        = 'Student ID';
$string['sortnophoto']        = 'No photo (missing first)';
$string['perpage']            = 'Photos per page';
$string['perpage_grid4']      = '4 × 5 (20 per page)';
$string['perpage_grid_all']   = 'All (max 100 per page)';
$string['nophotos']           = 'No student photos found for this course.';
$string['noresults']          = 'No students matched your search.';
$string['sisid']              = 'Student ID';
$string['email']              = 'Email';
$string['studentcount']       = '{$a} student(s) found';
$string['nophotocount']       = '{$a} student(s) missing a photo';
$string['showmissingonly']    = 'Show only students missing photos';
$string['showallstudents']    = 'Show all students';
$string['enlargephoto']       = 'View larger photo';
$string['nophotobadgetitle']  = 'No photo on file';
$string['nophotobadgetext']   = 'No photo';
$string['showall']            = 'Show all {$a}';
$string['showpaged']          = 'Show 20 per page';
$string['students']           = 'Students';
$string['pageinfo']           = "This page displays official York University (YU-Card) photos for students enrolled in this course.\n\n- These photos come from York's official YU-Card source.\n- They may differ from user-selected profile photos in eClass.\n- This view is visible only to instructors and non-editing instructors.";

// Upload page.
$string['uploadphoto']        = 'Upload Student Photo';
$string['uploadphototitle']   = 'YU Card Photo - Manual Upload';
$string['searchstudent']      = 'Search Moodle user';
$string['searchstudent_help'] = 'Enter a username, student ID (profile field), first name or last name to find a Moodle user.';
$string['selectuser']         = 'Select student';
$string['choosephoto']        = 'Photo file';
$string['choosephoto_help']   = 'Upload a JPEG or PNG photo for this student. Max 2 MB. The photo will be stored in the Moodle file system. If a photo already exists for this student it will be replaced.';
$string['overridephoto']      = 'Current photo (will be replaced)';
$string['uploadsubmit']       = 'Save photo';
$string['uploadsuccess']      = 'Photo saved successfully for {$a}.';
$string['uploaderror']        = 'Could not save the photo. Please try again.';
$string['usernotfound']       = 'No Moodle user matched that search term.';
$string['invalidfiletype']    = 'Only JPEG and PNG files are accepted.';
$string['minsearchchars']     = 'Please enter at least 3 characters to search.';
$string['noneselected']       = 'No student selected yet - search above and click a name.';

// Admin settings.
$string['settings_heading']        = 'YU Card Photo Import Settings — Oracle';
$string['disable_photo_view_globally'] = 'Disable Photo Roster globally';
$string['disable_photo_view_globally_desc'] = 'When enabled, the Photo Roster button is hidden for all courses and direct access to the Photo Roster page is blocked, even if a course has Photo Roster enabled.';
$string['yucard_db_type']          = 'External DB type';
$string['yucard_db_type_desc']     = 'Database driver to use when connecting to the external YU Card system. Select Oracle (OCI8) for the York YU Card database.';
$string['yucard_db_tns']           = 'Oracle TNS connect string';
$string['yucard_db_tns_desc']      = 'Easy Connect format: host:port/service_name — e.g. yucarddb3qa.yorku.yorku.ca:1521/bbts. Alternatively, a TNS alias defined in the server\'s tnsnames.ora.';
$string['yucard_db_schema']        = 'Oracle schema';
$string['yucard_db_schema_desc']   = 'The Oracle schema (owner) that contains the photo table. e.g. envision';
$string['yucard_db_user']          = 'External DB username';
$string['yucard_db_user_desc']     = 'Username used to connect to the external YU Card Oracle database.';
$string['yucard_db_pass']          = 'External DB password';
$string['yucard_db_pass_desc']     = 'Password for the external YU Card database user. Stored encrypted.';
$string['yucard_db_table']         = 'Source table / view name';
$string['yucard_db_table_desc']    = 'Name of the table or view in the Oracle schema that contains student photo data. e.g. YUCARDPHOTO';
$string['yucard_col_heading']      = 'Oracle Source Column Names';
$string['yucard_col_heading_desc'] = 'These map to the column names in the Oracle source table. First name and last name are not stored in the source — they are pulled from mdl_user joined on idnumber.';
$string['yucard_col_sisid']        = 'Student ID column';
$string['yucard_col_sisid_desc']   = 'Oracle column holding the student/YU Card number. Matched against mdl_user.idnumber to look up the student name. e.g. SISID';
$string['yucard_col_photo']        = 'Photo BLOB column';
$string['yucard_col_photo_desc']   = 'Oracle column containing the raw image binary (BLOB). e.g. PHOTO';
$string['yucard_col_updated']      = 'Last-updated timestamp column';
$string['yucard_col_updated_desc'] = 'Oracle column holding the last-modified date/timestamp for the photo. Used to skip unchanged records on subsequent imports. e.g. PHOTOMODIFIEDDATE';

// Privacy.
$string['privacy:metadata:local_yucardphoto']                       = 'The YU Card Photo Roster plugin stores photo references linked to a student identifier (SIS ID) and basic name information.';
$string['privacy:metadata:local_yucardphoto:sisid']                 = 'The student\'s SIS/student ID number.';
$string['privacy:metadata:local_yucardphoto:firstname']             = 'The student\'s first name as recorded in the YU Card system.';
$string['privacy:metadata:local_yucardphoto:lastname']              = 'The student\'s last name as recorded in the YU Card system.';
$string['privacy:metadata:local_yucardphoto:moodle_file_url']       = 'The Moodle file URL pointing to the stored photo in the Moodle file system.';
$string['privacy:metadata:local_yucardphoto:yucard_image_path']     = 'The internal file-system path of the stored photo within Moodle\'s file area.';
$string['privacy:metadata:local_yucardphoto:yucard_lastupdated']    = 'Unix timestamp of when the photo was last updated in the YU Card source system.';
$string['privacy:metadata:local_yucardphoto:timecreated']           = 'Unix timestamp of when this record was first created in Moodle.';
$string['privacy:metadata:local_yucardphoto:timemodified']          = 'Unix timestamp of when this record was last modified in Moodle.';
$string['privacy:metadata:external_yucard_db']                      = 'Photo data (binary image blobs) and basic student information are read from an external YU Card database. No data is written back to that system.';

// Cleanup admin page.
$string['cleanup_title']               = 'YU Card Photo Cleanup';
$string['cleanup_tools_heading']       = 'Tools';
$string['cleanup_back_settings']       = '← YU Card Photo Roster';
$string['cleanup_stats_heading']       = 'Storage Summary';
$string['cleanup_stat_dbrows']         = 'DB records';
$string['cleanup_stat_files']          = 'Stored files';
$string['cleanup_stat_orphans']        = 'Orphaned files';
$string['cleanup_stat_stale']          = 'Stale DB rows';
$string['cleanup_stat_totalsize']      = 'Total file storage';
$string['cleanup_orphan_detail']       = 'Orphaned files — file exists but no DB record';
$string['cleanup_stale_detail']        = 'Stale DB rows — record exists but file is missing';
$string['cleanup_col_filename']        = 'Filename';
$string['cleanup_col_size']            = 'Size';
$string['cleanup_col_firstname']       = 'First name';
$string['cleanup_col_lastname']        = 'Last name';
$string['cleanup_actions_heading']     = 'Cleanup Actions';
$string['cleanup_action_orphans']      = 'Delete orphaned files';
$string['cleanup_action_orphans_desc'] = 'Removes files stored on the Moodle file system that have no matching record in the yucardphoto table. This can happen if a DB row was deleted manually without removing the file.';
$string['cleanup_action_orphans_btn']  = 'Delete orphaned files';
$string['cleanup_confirm_orphans']     = 'Delete all orphaned photo files? This cannot be undone.';
$string['cleanup_action_stale']        = 'Delete stale DB rows';
$string['cleanup_action_stale_desc']   = 'Removes records from the yucardphoto table where the associated file no longer exists on the file system. The next import task run will re-import any photos that are still in the Oracle source.';
$string['cleanup_action_stale_btn']    = 'Delete stale rows';
$string['cleanup_confirm_stale']       = 'Delete all stale DB rows with no associated file? This cannot be undone.';
$string['cleanup_action_sisids']       = 'Delete photos for specific Student IDs';
$string['cleanup_action_sisids_desc']  = 'Enter one or more Student IDs (comma-separated) to delete their photo file and DB record.';
$string['cleanup_action_sisids_btn']   = 'Delete';
$string['cleanup_confirm_sisids']      = 'Delete photos for the listed Student IDs? This cannot be undone.';
$string['cleanup_sisids_placeholder']  = 'e.g. 200502815, 200507111';
$string['cleanup_action_all']          = 'Delete ALL photos';
$string['cleanup_action_all_desc']     = 'Removes every photo file from the Moodle file system and deletes all records from the yucardphoto table. Use this to fully reset before a clean import. The next scheduled task run will re-import everything from Oracle.';
$string['cleanup_action_all_btn']      = 'Delete ALL photos and records';
$string['cleanup_confirm_all']         = 'This will permanently delete ALL stored photos and ALL yucardphoto records. Are you absolutely sure?';
$string['cleanup_nothing']             = 'No photos are currently stored — nothing to clean up.';
$string['cleanup_deleted_all']         = 'Deleted {$a->files} file(s) and {$a->rows} DB record(s).';
$string['cleanup_deleted_orphans']     = 'Deleted {$a} orphaned file(s).';
$string['cleanup_deleted_stale']       = 'Deleted {$a} stale DB row(s).';
$string['cleanup_deleted_sisids']      = 'Deleted {$a->files} file(s) and {$a->rows} record(s). Not found: {$a->notfound}';
$string['cleanup_nosisids']            = 'Please enter at least one Student ID.';

