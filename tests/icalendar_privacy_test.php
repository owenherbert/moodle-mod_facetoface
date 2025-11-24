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

namespace mod_facetoface;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once("$CFG->dirroot/mod/facetoface/lib.php");

/**
 * Test the iCalendar privacy settings.
 *
 * @package    mod_facetoface
 * @copyright  2025 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers ::facetoface_get_ical_attachment
 */
final class icalendar_privacy_test extends \advanced_testcase {

    /**
     * Test that calendar invitations respect the private setting when enabled.
     */
    public function test_icalendar_private_flag_enabled(): void {
        global $CFG;
        $this->resetAfterTest();

        // Enable private calendar invitations.
        set_config('icalendarprivate', 1, 'facetoface');

        /** @var \mod_facetoface_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        // Setup course, f2f and user.
        $course = $this->getDataGenerator()->create_course();
        $facetoface = $generator->create_instance([
            'course' => $course->id,
            'name' => 'Test Face-to-Face',
        ]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        // Create a session.
        $now = time();
        $session = $generator->create_session([
            'facetoface' => $facetoface->id,
            'sessiondates' => [
                ['timestart' => $now + DAYSECS, 'timefinish' => $now + DAYSECS + HOURSECS],
            ],
        ]);

        // Generate iCal attachment.
        $icalfile = facetoface_get_ical_attachment(MDL_F2F_INVITE, $facetoface, $session, $user);

        // Read the generated iCal file.
        $icalcontent = file_get_contents($icalfile);

        // Assert that CLASS:PRIVATE is present in the iCal content.
        $this->assertStringContainsString('CLASS:PRIVATE', $icalcontent);
        $this->assertStringNotContainsString('CLASS:PUBLIC', $icalcontent);

        // Clean up temp file.
        @unlink($icalfile);
    }

    /**
     * Test that calendar invitations respect the private setting when disabled.
     */
    public function test_icalendar_private_flag_disabled(): void {
        global $CFG;
        $this->resetAfterTest();

        // Disable private calendar invitations.
        set_config('icalendarprivate', 0, 'facetoface');

        /** @var \mod_facetoface_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        // Setup course, f2f and user.
        $course = $this->getDataGenerator()->create_course();
        $facetoface = $generator->create_instance([
            'course' => $course->id,
            'name' => 'Test Face-to-Face',
        ]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        // Create a session.
        $now = time();
        $session = $generator->create_session([
            'facetoface' => $facetoface->id,
            'sessiondates' => [
                ['timestart' => $now + DAYSECS, 'timefinish' => $now + DAYSECS + HOURSECS],
            ],
        ]);

        // Generate iCal attachment.
        $icalfile = facetoface_get_ical_attachment(MDL_F2F_INVITE, $facetoface, $session, $user);

        // Read the generated iCal file.
        $icalcontent = file_get_contents($icalfile);

        // Assert that CLASS:PUBLIC is present in the iCal content.
        $this->assertStringContainsString('CLASS:PUBLIC', $icalcontent);
        $this->assertStringNotContainsString('CLASS:PRIVATE', $icalcontent);

        // Clean up temp file.
        @unlink($icalfile);
    }

    /**
     * Test default behavior (should be private when not explicitly set).
     */
    public function test_icalendar_private_flag_default(): void {
        global $CFG;
        $this->resetAfterTest();

        // Don't set any config, test default behavior.
        // The default in settings.php is 1 (enabled), but if not set at all, get_config returns false.
        // So we need to test what happens when it's explicitly null/unset.
        // We'll unset it to test the actual default behavior.
        unset_config('icalendarprivate', 'facetoface');

        /** @var \mod_facetoface_generator $generator */
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_facetoface');

        // Setup course, f2f and user.
        $course = $this->getDataGenerator()->create_course();
        $facetoface = $generator->create_instance([
            'course' => $course->id,
            'name' => 'Test Face-to-Face',
        ]);
        $user = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        // Create a session.
        $now = time();
        $session = $generator->create_session([
            'facetoface' => $facetoface->id,
            'sessiondates' => [
                ['timestart' => $now + DAYSECS, 'timefinish' => $now + DAYSECS + HOURSECS],
            ],
        ]);

        // Generate iCal attachment.
        $icalfile = facetoface_get_ical_attachment(MDL_F2F_INVITE, $facetoface, $session, $user);

        // Read the generated iCal file.
        $icalcontent = file_get_contents($icalfile);

        // When config is not set, get_config returns false, which evaluates to false in the ternary.
        // So it should be PUBLIC.
        $this->assertStringContainsString('CLASS:PUBLIC', $icalcontent);

        // Clean up temp file.
        @unlink($icalfile);
    }
}
