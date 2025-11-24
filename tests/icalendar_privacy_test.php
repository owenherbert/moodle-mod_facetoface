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
     * Test behavior when config is not set.
     * 
     * Note: The default value in settings.php (1) only applies when the plugin is installed
     * or when an admin saves the settings. In a test environment where the config has never
     * been set, get_config returns false, which results in PUBLIC behavior.
     */
    public function test_icalendar_private_flag_when_unset(): void {
        $this->resetAfterTest();

        // Explicitly unset the config to test behavior when not configured.
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
        // Therefore it should be PUBLIC.
        $this->assertStringContainsString('CLASS:PUBLIC', $icalcontent);

        // Clean up temp file.
        @unlink($icalfile);
    }
}
