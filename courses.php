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
 * Show courses using a resource.
 *
 * @package     local_sharedresources
 * @author      Valery Fremaux <valery@gmail.com>
 * @copyright   Valery Fremaux (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

require('../../config.php');
require($CFG->dirroot.'/local/sharedresources/lib.php');

$courseid = optional_param('course', SITEID, PARAM_INT); // Optional course if we are comming from a course.
$repo = optional_param('repo', '', PARAM_TEXT);
$entryid = required_param('entryid', PARAM_INT);

$params = [
    'course' => $courseid,
    'repo' => $repo,
    'entryid' => $entryid,
];
$PAGE->set_url('/local/sharedresources/courses.php', $params);

// Security.

if ($courseid) {
    $context = context_course::instance($courseid);
} else {
    $context = context_system::instance();
}

require_login();
require_capability('repository/sharedresources:view', $context);

// Prepare the page.

$PAGE->set_context($context);
$PAGE->navbar->add(get_string('library', 'local_sharedresources'));
$PAGE->set_title(get_string('courselist', 'local_sharedresources'));
$PAGE->set_heading(get_string('courselist', 'local_sharedresources'));

$renderer = $PAGE->get_renderer('local_sharedresources');

$entryrec = $DB->get_record('sharedresource_entry', ['id' => $entryid]);
$courses = sharedresources_get_courses($entryrec);

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('resourceusage', 'local_sharedresources'));

echo $OUTPUT->box_start();

if (!empty($courses)) {
    echo '<ul>';
    foreach ($courses as $c) {
        echo $renderer->resource_course($c);
    }
    echo '</ul>';
}

echo $OUTPUT->box_end();

echo '<center>';

$buttonurl = new moodle_url('/local/sharedresource/index.php', ['course' => $courseid, 'repo' => $repo]);
echo $OUTPUT->single_button($buttonurl, get_string('backtoindex', 'local_sharedresources'));

echo '</center>';

echo $OUTPUT->footer();
