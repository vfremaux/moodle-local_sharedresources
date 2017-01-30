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
 *
 * @author  Frédéric GUILLOU
 * @version 0.0.1
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License, mod/sharedresource is a work derived from Moodle mod/resource
 * @package local_sharedresources
 * @status May be obsolete code
 */

// This php script is called using ajax
// It display a page of resources
//-----------------------------------------------------------

require('../../../config.php');
require_once($CFG->dirroot.'/local/sharedresources/lib.php');

$page = required_param('page', PARAM_INT);
$numpage = required_param('numpage', PARAM_INT);
$isediting = required_param('isediting', PARAM_TEXT);
$courseid = required_param('courseid', PARAM_INT);
$repo = required_param('repo', PARAM_TEXT);

if ($courseid) {
    $course = $DB->get_record('course', array('id' => $courseid));
} else {
    $course = null;
}

$resources = $SESSION->resourceresult;
$tempresources = array();
$i = 1;
$beginprint = (($numpage - 1) * $page) + 1;
$endprint = $beginprint + $page;

foreach ($resources as $id => $value) {
    if ($i >= $beginprint && $i < $endprint) {
        if (count($tempresources) < $page) {
            $tempresources[$id] = $value;
        }
    }
    $i++;
}
resources_browse_print_list($tempresources, $course, $isediting, $repo);
