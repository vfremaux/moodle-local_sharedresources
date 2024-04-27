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
 * @package local_sharedresources
 * @category local
 *
 * Get a resource version description.
 */
require('../../../config.php');
require_once($CFG->dirroot.'/local/sharedresources/lib.php');

$resid = required_param('resid', PARAM_TEXT);
$courseid = required_param('courseid', PARAM_INT);
$isediting = optional_param('isediting', '', PARAM_BOOL);
$shrtemplate = optional_param('template', 'boxresourcebodyinner', PARAM_TEXT);
$repo = optional_param('repo', $CFG->mnet_localhost_id, PARAM_INT); // Repo is given as mnethostid.

// Defined by page format
if (!defined('RETURN_PAGE')) {
    define('RETURN_PAGE', get_config('local_sharedresources', 'defaultlibraryindexpage'));
}

$context = context_system::instance();
$PAGE->set_context($context);
if (!empty($config->privatecatalog)) {
    if ($courseid) {
        $context = context_course::instance($courseid);
        $course = $DB->get_record('course', array('id' => $courseid));
        require_login($course);
    } else {
        $context = context_system::instance();
        require_login();
    }

    if (!sharedresources_has_capability_somewhere('repository/sharedresources:view', false, false, false, CONTEXT_COURSECAT.','.CONTEXT_COURSE)) {
        throw new moodle_exception(get_string('noaccess', 'local_sharedresource'));
    }
}

if ($repo == 'local' || $repo = $CFG->mnet_localhost_id) {
    $repohostroot = $CFG->wwwroot;
} else {
    $repohostroot = $DB->get_field('mnet_host', 'wwwroot', array('id' => $repo));
}

if ($repohostroot == $CFG->wwwroot) {

    $renderer = $PAGE->get_renderer('local_sharedresources');
    $version = $DB->get_record('sharedresource_entry', ['identifier' => $resid], '*', MUST_EXIST);
    $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
    $gui = $renderer->get_gui();
    $gui->bodytplname = $shrtemplate; // Will have to resolve dynamically the template.
    $resourcedesc = $renderer->print_resource($version, $course, $repo, $isediting, $gui);
} else {
    // later implementation.
}

echo $resourcedesc;