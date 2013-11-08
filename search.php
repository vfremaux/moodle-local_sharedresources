<?php
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
 * @package    mod-sharedresource
 * @subpackage resources
 * @author Valery Fremaux <valery@valeisti.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 * Provides a pluggable search form for several external resources repositories.
 * @see resources/results.php
 */

    /**
    * Includes and requires
    */
    require_once('../config.php');
    require_once($CFG->dirroot.'/resources/lib.php');
    require_js($CFG->wwwroot.'/mod/sharedresource/js/calendar.js');

/// Parameters

    $repo = optional_param('repo', 'cndp', PARAM_TEXT);
    $courseid = optional_param('id', SITEID, PARAM_INT);

/// Build and print header

    $searchquerystr = get_string('remotesearchquery', 'sharedresource');

    $course = get_record('course', 'id', $courseid);
    
    $strtitle =get_string('classificationconfiguration', 'sharedresource');
    $PAGE->set_pagelayout('standard');
    $PAGE->set_context($system_context);
    $PAGE->set_title($strtitle);
    $PAGE->set_heading($SITE->fullname);
    /* SCANMSG: may be additional work required for $navigation variable */
    $PAGE->navbar->add( $course->shortname,$CFG->wwwroot."/course/view.php?id=".$courseid,'misc');
    $PAGE->navbar->add($strtitle,'search.php','misc');

    $PAGE->set_focuscontrol('');
    $PAGE->set_cacheable(false);
    $PAGE->set_button('');
    $PAGE->set_headingmenu('');

    $url = new moodle_url('/local/sharedresources/search.php');
    $PAGE->set_url($url);
    print($OUTPUT->header()); 
  /// get repos and make tabs

    resources_search_print_tabs($repo, $course);

/// get repo and get search page
    
    add_to_log(0, 'resources', "search/{$repo}", $CFG->wwwroot."/resources/search.php?repo={$repo}&id={$courseid}", 0);

    include $CFG->dirroot."/resources/plugins/{$repo}/remotesearch.php";

/// Footer.

    print_footer($course);
?>