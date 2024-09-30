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
 * Search engine.
 *
 * @package     local_sharedresources
 * @author      Valery Fremaux <valery@gmail.com>
 * @copyright   Valery Fremaux (activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

 /*
 * Provides a pluggable search form for several external resources repositories.
 * @see resources/results.php
 */
require('../../config.php');
require_once($CFG->dirroot.'/local/sharedresources/lib.php');

$PAGE->requires->js('/mod/sharedresource/js/calendar.js');

$repo = optional_param('repo', 'cndp', PARAM_TEXT);
$courseid = optional_param('id', SITEID, PARAM_INT);
$config = get_config('local_sharedresources');

$searchquerystr = get_string('remotesearchquery', 'local_sharedresources');

if (!$course = $DB->get_record('course', ['id' => $courseid])) {
    throw new moodle_exception(get_string('coursemisconf'));
}

$context = context_system::instance();
if (!empty($config->privatecatalog)) {

    if ($courseid) {
        $context = context_course::instance($courseid);
        require_login($course);
    } else {
        require_login();
    }
    $where = CONTEXT_COURSECAT.','.CONTEXT_COURSE;
    if (!sharedresources_has_capability_somewhere('repository/sharedresources:view', false, false, false, $where)) {
        throw new moodle_exception(get_string('noaccess', 'local_sharedresources'));
    }
}

$strtitle = get_string('search', 'local_sharedresources');
$PAGE->set_pagelayout('standard');
$PAGE->set_context($context);
$PAGE->set_title($strtitle);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add( $course->shortname, new moodle_url('/course/view.php', ['id' => $courseid]));
$PAGE->navbar->add($strtitle, new moodle_url('/local/sharedresources/search.php', ['id' => $courseid]));

$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(false);
$PAGE->set_button('');
$PAGE->set_headingmenu('');

$url = new moodle_url('/local/sharedresources/search.php');
$PAGE->set_url($url);

echo $OUTPUT->header();

// Get repos and make tabs.

resources_search_print_tabs($repo, $course);


// Get repo and get search page.
require($CFG->dirroot."/resources/plugins/{$repo}/remotesearch.php");

echo $OUTPUT->footer();
