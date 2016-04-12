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
 * @package    mod-taoresource
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
    require_once('../../config.php');
    require_once($CFG->dirroot.'/local/sharedresources/lib.php');
    
/// Parameters
    
    $courseid = optional_param('id', SITEID, PARAM_INT);
    if ($courseid == 0) $courseid = SITEID;
    $repo = required_param('repo', PARAM_TEXT);

    if (!$course = get_record('course', 'id', $courseid)) {
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