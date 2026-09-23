<?php
/**
 * Test script for degree course detection.
 *
 * Mirrors the current plugin logic in `local_yucardphoto_is_degree_course()`:
 * - token 1 must be a 4-digit year,
 * - the idnumber must contain at least 5 underscore-delimited tokens, and
 * - the first character of token 5 must be 1-9.
 *
 * Run this from the CLI to verify the logic.
 *
 * Usage: php TEST_DEGREE_COURSE_DETECTION.php
 */

// Simulate the degree course detection logic.
function test_is_degree_course($idnumber) {
    if (empty($idnumber)) {
        return false;
    }

    $parts = explode('_', $idnumber);

    if (count($parts) < 5) {
        return false;
    }

    // Validate that the first token (year) is numeric (4-digit year)
    if (!is_numeric($parts[0]) || strlen($parts[0]) !== 4) {
        return false;
    }

    // The course level is encoded as the first character of the 5th token.
    $courselevel = substr($parts[4], 0, 1);
    return preg_match('/^[1-9]$/', $courselevel) === 1;
}

// Test cases
$testcases = [
    // Degree courses (should return TRUE)
    [
        'idnumber' => '2026_AP_ADMS_F_3510',
        'expected' => true,
        'description' => 'Minimal valid York-style idnumber; 5th token starts with 3'
    ],
    [
        'idnumber' => '2026_AP_ADMS_F_3510__3_ABmerged_EN_A_LECT_01',
        'expected' => true,
        'description' => 'Extended York-style idnumber; 5th token starts with 3'
    ],
    [
        'idnumber' => '2026_SC_PHYS_F_1101A__1_01_E_Y_LEC_01',
        'expected' => true,
        'description' => 'Level 1 (Undergraduate) - PHYS course'
    ],
    [
        'idnumber' => '2026_GL_GEOS_W_5320__5_01_E_Y_LEC_01',
        'expected' => true,
        'description' => 'Level 5 (Graduate) - GEOS course'
    ],
    [
        'idnumber' => '2025_FA_MUSC_F_2090__9_A_E_Y_LEC_01',
        'expected' => true,
        'description' => 'Level 9 (Graduate) - MUSC course'
    ],

    // Non-degree courses (should return FALSE)
    [
        'idnumber' => '2026_AP_ADMS_F_PD100__0_01_EN_A_LECT_01',
        'expected' => false,
        'description' => 'Professional development style idnumber; 5th token starts with P'
    ],
    [
        'idnumber' => '2026_AP_ADMS_F_0510',
        'expected' => false,
        'description' => '5th token starts with 0, so it is not a degree course'
    ],
    [
        'idnumber' => 'TEST_COURSE',
        'expected' => false,
        'description' => 'Invalid format - too few underscores'
    ],
    [
        'idnumber' => '',
        'expected' => false,
        'description' => 'Empty idnumber'
    ],
    [
        'idnumber' => 'NOYEAR_AP_ADMS_F_3510__3_01_E_Y_LEC_01',
        'expected' => false,
        'description' => 'Non-numeric year'
    ],
    [
        'idnumber' => '2026_AP_ADMS_F__3_01_E_Y_LEC_01',
        'expected' => false,
        'description' => 'Empty 5th token'
    ],
];

// Run tests
echo "=== Degree Course Detection Tests ===\n\n";

$passed = 0;
$failed = 0;

foreach ($testcases as $test) {
    $result = test_is_degree_course($test['idnumber']);
    $status = ($result === $test['expected']) ? '✅ PASS' : '❌ FAIL';

    if ($result === $test['expected']) {
        $passed++;
    } else {
        $failed++;
    }

    printf("%s | %s\n", $status, $test['description']);
    printf("   IDNumber: %s\n", $test['idnumber']);
    printf("   Expected: %s | Got: %s\n\n",
        ($test['expected'] ? 'DEGREE' : 'NON-DEGREE'),
        ($result ? 'DEGREE' : 'NON-DEGREE')
    );
}

echo "=== Summary ===\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total:  " . ($passed + $failed) . "\n";

if ($failed === 0) {
    echo "\n✅ All tests passed! The detection logic is working correctly.\n";
} else {
    echo "\n❌ Some tests failed. Please review the logic.\n";
}


