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
 * This file provides access to a master shared resources index, intending
 * to allow a public browsing of resources.
 * The catalog is considered as multi-provider, and can federate all resources into
 * browsing results, or provide them as separate catalogs for each resource provider.
 *
 * The index admits browsing remote linked catalogues, and will aggregate the found
 * entries in the current view, after a contextual query has been fired to remote connected
 * resource sets.
 *
 * The index will provide a "top viewed" resources side tray, and a "top used" side tray,
 * that will count local AND remote inttegration of the resource. The remote query to
 * bound catalogs will also get information about local catalog resource used by remote courses.
 *
 * The index is public access. Browsing the catalog should although be done through a Guest identity,
 * having as a default the repository/sharedresources:manage capability.
 */

/**
 * @package     local_sharedresource
 * @category    local
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 */
require('../../config.php');
require_once($CFG->libdir.'/blocklib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/local/sharedresources/lib.php');
require_once($CFG->dirroot.'/local/sharedresources/pushout_form.php');

$course = optional_param('course', '', PARAM_INT);
$resourceid = required_param('resourceid', PARAM_INT);
$repo = optional_param('repo', 'local', PARAM_TEXT);

// Security.

$systemcontext = context_system::instance();
require_login();
require_capability('repository/sharedresources:manage', $systemcontext);

$resourcesmoodlestr = get_string('resources', 'sharedresource');

$url = new moodle_url('/local/sharedresources/pushout.php', array('course' => $course));
$PAGE->set_url($url);
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($resourcesmoodlestr);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('resourcesadministration', 'local_sharedresources'));
$PAGE->navbar->add(get_string('resourcespushout', 'local_sharedresources'));

// Setup the dialog for pushing out.
$form = new PushOut_Form($resourceid);

if ($form->is_cancelled()) {
    redirect(new moodle_url('/local/sharedresources/index.php', array('course' => $course)));
}

if ($data = $form->get_data()) {
    // Do the real thing !!
    $resourceentry = $DB->get_record('sharedresource_entry', array('id' => $resourceid));
    sharedresource_submit($data->provider, $resourceentry);
    redirect(new moodle_url('/local/sharedresources/index.php', array('course' => $course)));
    die;
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->box_start('generalbox');
    $form->display();
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
}
