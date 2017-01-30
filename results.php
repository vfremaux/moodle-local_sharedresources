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
 * @package     local_sharedresources
 * @category    local
 * @author      Valery Fremaux <valery@valeisti.fr>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 * Provides a pluggable search form for several external resources repositories.
 * @see resources/results.php
 */
require('../../config.php');
require_once($CFG->dirroot.'/local/sharedresources/lib.php');

$courseid = optional_param('id', SITEID, PARAM_INT);

if ($courseid == 0) {
    $courseid = SITEID;
}

if ($course == SITEID) {
    $context = context_system::instance();
} else {
    $context = context_course::instance($courseid);
}

$repo = required_param('repo', PARAM_TEXT);

$url = new moodle_url('/local/sharedresources/results.php', array('id' => $id));
$PAGE->set_url($url);
$PAGE->set_context($context);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('coursemisconf');
}

require_login();

// Build and print header.

$searchresultsstr = get_string('remotesearchresults', 'sharedresource');

$PAGE->set_heading($SITE->fullname);
if ($courseid) {
    $PAGE->navbar->add($course->shortname, new moodle_url('/course/view.php', array('id' => $courseid)));
}
$PAGE->navbar->add($searchresultsstr);

echo $OUTPUT->header();

// Get repos and make tabs.

resources_search_print_tabs($repo, $course);

// Get repo and get search page.

echo $OUTPUT->heading($searchresultsstr);

include($CFG->dirroot."/local/sharedresources/plugins/{$repo}/remoteresults.php");

echo '<center>';
$buttonurl = new moodle_url.'/local/sharedresources/search.php', array('repo' => $repo, 'id' => $courseid));
echo $OUTPUT->single_button($buttonurl, get_string('othersearch', 'sharedresource'));
echo $OUTPUT->continue_button($CFG->wwwroot);
echo '</center>';

echo $OUTPUT->footer();
