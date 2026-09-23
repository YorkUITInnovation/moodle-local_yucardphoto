# YU Card Photo Feature: Degree Course Restriction Implementation

## Summary
The `local_yucardphoto` Photo View feature has been successfully restricted to **degree courses only**. Non-degree courses (professional development, special topics, etc.) will no longer have access to the Photo View settings.

## Implementation Details

### Changes Made

#### 1. **lib.php** - Added Helper Function
File: `/public/local/yucardphoto/lib.php`

Added new function: `local_yucardphoto_is_degree_course(int $courseid): bool`

This function:
- Reads the course `idnumber` from `mdl_course`
- Splits it on underscores and inspects the first character of the 5th token
- Treats levels `1`-`9` as degree courses
- Returns `true` for degree courses, `false` for non-degree courses
- Is the single source of truth for degree course detection

```php
function local_yucardphoto_is_degree_course(int $courseid): bool {
    global $DB;

    $course = $DB->get_record('course', ['id' => $courseid], 'idnumber');

    if (!$course || empty($course->idnumber)) {
        return false;
    }

    $parts = explode('_', $course->idnumber);
    if (count($parts) < 5) {
        return false;
    }

    if (!is_numeric($parts[0]) || strlen($parts[0]) !== 4) {
        return false;
    }

    $courselevel = substr($parts[4], 0, 1);
    return preg_match('/^[1-9]$/', $courselevel) === 1;
}
```

#### 2. **after_form_definition.php** - Hide Settings for Non-Degree Courses
File: `/public/local/yucardphoto/classes/hook/course/after_form_definition.php`

Added degree course check before displaying the "Participant Photograph" section:

```php
// Only show for degree courses. Professional development and other
// non-degree courses are not eligible for Photo View.
require_once($CFG->dirroot . '/local/yucardphoto/lib.php');
if (!\local_yucardphoto_is_degree_course((int)$course->id)) {
    return;
}
```

**Effect**: Instructors editing non-degree course settings will not see the Photo View option.

#### 3. **after_form_submission.php** - Prevent Saving for Non-Degree Courses
File: `/public/local/yucardphoto/classes/hook/course/after_form_submission.php`

Added safety check to prevent saving settings for non-degree courses:

```php
// Extra safety: only allow setting for degree courses
require_once($CFG->dirroot . '/local/yucardphoto/lib.php');
if (!\local_yucardphoto_is_degree_course($courseid)) {
    return;
}
```

**Effect**: Even if someone tries to bypass the UI, the setting won't be saved for non-degree courses.

#### 4. **before_http_headers.php** - Don't Show Button When the Roster Is Not Available
File: `/public/local/yucardphoto/classes/hook/output/before_http_headers.php`

The button hook now uses the shared roster visibility helper:

```php
if (!\local_yucardphoto_can_show_roster($courseid, $context)) {
    return;
}
```

**Effect**: The Photo View button will only appear when all of the following are true:

- the feature is not globally disabled,
- the course is a degree course,
- the course-level Photo View setting is enabled, and
- the current user has `local/yucardphoto:viewroster`.

#### 5. **participants.php** - Block Direct Access Using the Same Shared Rule
File: `/public/local/yucardphoto/participants.php`

Direct access to the roster page now uses the same helper as the button hook:

```php
if (!local_yucardphoto_can_show_roster($courseid, $context)) {
    throw new \moodle_exception('nopermissions', 'error', '', get_string('photoview', 'local_yucardphoto'));
}
```

**Effect**: The global disable option, degree-course restriction, course-level enablement, and capability check are enforced consistently for both the button and the page itself.

## Degree Course Detection Logic

### Degree Levels (1-9)
Courses are classified as "degree" if their `course.idnumber` matches the York pattern and the first character of the 5th underscore-delimited token is a single digit `1`-`9`:

- **1, 2, 3, 4** = Undergraduate degree courses
- **5, 6, 7, 8, 9** = Graduate degree courses

### Non-Degree Courses
Courses are treated as non-degree when any of the following is true:

- no `idnumber` is present,
- the `idnumber` does not have at least 5 underscore-delimited tokens,
- the first token is not a 4-digit numeric year, or
- the first character of the 5th token is not `1`-`9`.

- Professional development (e.g., Osgoode Professional Development programs)
- Special topics
- Continuing education
- External courses

## Testing Checklist

- [ ] Edit a degree course (e.g., 2024_SC_BIOL_F_2301...) → Photo View settings should appear
- [ ] Edit a non-degree course (e.g., professional development) → Photo View settings should NOT appear
- [ ] Enable Photo View on a degree course → Setting should save successfully
- [ ] Try to enable Photo View on non-degree course → Setting should be ignored
- [ ] Enable the site-wide **Disable Photo View globally** setting → Button should be hidden and direct access to `/local/yucardphoto/participants.php` should be blocked for every course
- [ ] Visit participants page for degree course with Photo View enabled → Button should appear
- [ ] Visit participants page for non-degree course with Photo View enabled → Button should NOT appear

## Database Queries for Verification

### Get degree courses with Photo View enabled:
```sql
SELECT c.id, c.idnumber, c.fullname, uyp.enabled
FROM {course} c
LEFT JOIN {local_yucardphoto_coursesettings} uyp ON uyp.courseid = c.id
WHERE REGEXP_LIKE(c.idnumber, '^[0-9]{4}_[^_]*_[^_]*_[^_]*_[1-9]')
  AND uyp.enabled = 1;
```

### Get non-degree courses (should have no Photo View settings):
```sql
SELECT c.id, c.idnumber, c.fullname
FROM {course} c
WHERE c.idnumber IS NULL
   OR NOT REGEXP_LIKE(c.idnumber, '^[0-9]{4}_[^_]*_[^_]*_[^_]*_[1-9]');
```

## References

- See `docs/TEST_DEGREE_COURSE_DETECTION.php` for sample degree-course detection cases
- Shared visibility rule: `local_yucardphoto_can_show_roster()` in `/public/local/yucardphoto/lib.php`
- Course idnumber format: `YEAR_FACULTY_DEPARTMENT_PERIOD_LEVEL...`

## Rollback Instructions

If needed, revert changes in these files:
1. `/public/local/yucardphoto/lib.php` - Remove the `local_yucardphoto_is_degree_course()` function
2. `/public/local/yucardphoto/classes/hook/course/after_form_definition.php` - Remove the degree check
3. `/public/local/yucardphoto/classes/hook/course/after_form_submission.php` - Remove the degree check
4. `/public/local/yucardphoto/classes/hook/output/before_http_headers.php` - Restore the older inline visibility checks
5. `/public/local/yucardphoto/participants.php` - Restore the older inline access checks

