<?php
<<<<<<< HEAD
/**
 * Moodle - Modular Object-Oriented Dynamic Learning Environment
 *          http://moodle.org
 * Copyright (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mod-taoresource
 * @subpackage resources
=======
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
 * @package    local_sharedresources
 * @category   local
>>>>>>> MOODLE_33_STABLE
 * @author Valery Fremaux <valery@valeisti.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 * Provides a pluggable search form for several external resources repositories.
 * @see resources/results.php
 */
<<<<<<< HEAD

    /**
    * Includes and requires
    */    
    require_once('../../config.php');
    require_once($CFG->dirroot.'/local/sharedresources/lib.php');
    
/// Parameters
    
    $courseid = optional_param('id', SITEID, PARAM_INT);
    if ($courseid == 0) $courseid = SITEID;
    $repo = required_param('repo', PARAM_TEXT);

    if (!$course = get_record('course', 'id', $courseid)){
		print_error('coursemisconf');
    }

/// Build and print header

    $searchresultsstr = get_string('remotesearchresults', 'sharedresource');

    $navlinks[] = array('name' => $course->shortname, 'url' => $CFG->wwwroot."/course/view.php?id={$courseid}", 'type' => 'link');

    $navlinks[] = array('name' => $searchresultsstr, 'url' => '', 'type' => 'title');
    $navigation = build_navigation($navlinks);
    print_header_simple($SITE->fullname, '', $navigation, '', '', true,'', '', '', 'class="remote-search"');

/// get repos and make tabs

    resources_search_print_tabs($repo, $course);

/// get repo and get search page
    
    add_to_log(0, 'resources', "search/{$repo}", $CFG->wwwroot."/resources/search.php?repo={$repo}&id={$courseid}", 0);
    
    print_heading($searchresultsstr);

    include $CFG->dirroot."/resources/plugins/{$repo}/remoteresults.php";

    echo '<center>';
    print_single_button($CFG->wwwroot.'/resources/search.php', array('repo' => $repo, 'id' => $courseid), get_string('othersearch', 'sharedresource'));
    print_continue($CFG->wwwroot);
    echo '</center>';

    print_footer($course);

?>
=======
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
$params = array('repo' => $repo, 'id' => $courseid), get_string('othersearch', 'sharedresource');
echo $OUTPUT->single_button(new moodle_url.'/local/sharedresources/search.php', $params);
echo $OUTPUT->continue_button($CFG->wwwroot);
echo '</center>';

echo $OUTPUT->footer();
>>>>>>> MOODLE_33_STABLE
