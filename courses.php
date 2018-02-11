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
 * @package     local_sharedresource
 * @category    local
 * @author      Valery Fremaux (valery.fremaux@gmail.com)
 *
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
 * having as a default the repository/sharedresources:view capability.
 */
require('../../config.php');
require($CFG->dirroot.'/local/sharedresources/lib.php');

$courseid = optional_param('course', SITEID, PARAM_INT); // Optional course if we are comming from a course.
$repo = optional_param('repo', '', PARAM_TEXT);
$entryid = required_param('entryid', PARAM_INT);

$params = array('course' => $courseid,
                'repo' => $repo,
                'entryid' => $entryid);
$PAGE->set_url('/local/sharedresources/courses.php', $params);

// Security.

if ($courseid) {
    $context = context_course::instance($courseid);
} else {
    $context = context_system::instance();
}

require_capability('repository/sharedresources:view', $context);

// Prepare the page.

$PAGE->set_context($context);
$PAGE->navbar->add(get_string('library', 'local_sharedresources'));
$PAGE->set_title(get_string('courselist', 'local_sharedresources'));
$PAGE->set_heading(get_string('courselist', 'local_sharedresources'));

$renderer = $PAGE->get_renderer('local_sharedresources');

$entryrec = $DB->get_record('sharedresource_entry', array('id' => $entryid));
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

$buttonurl = new moodle_url('/local/sharedresource/index.php', array('course' => $courseid, 'repo' => $repo));
echo $OUTPUT->single_button($buttonurl, get_string('backtoindex', 'local_sharedresources'));

echo '</center>';

echo $OUTPUT->footer();