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
require_once($CFG->libdir.'/blocklib.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/rpclib.php');
require_once($CFG->dirroot.'/mod/sharedresource/classes/search_widget.class.php');
require_once($CFG->dirroot.'/local/sharedresources/lib.php');

$PAGE->requires->jquery();
$PAGE->requires->js('/local/sharedresources/js/search.js', true);

$config = get_config('sharedresource');

$edit = optional_param('edit', -1, PARAM_BOOL);
$blockaction = optional_param('blockaction', '', PARAM_ALPHA);
$courseid = optional_param('course', SITEID, PARAM_INT); // Optional course if we are comming from a course.
$section = optional_param('section', '', PARAM_INT); // Optional course section if we are searhcing for feeding a section.
$repo = optional_param('repo', 'local', PARAM_TEXT);
$offset = optional_param('offset', 0, PARAM_INT);
$action = optional_param('what', '', PARAM_TEXT);

$PAGE->requires->js_call_amd('local_sharedresources/library', 'init', array('repo' => $repo));

// Security.

if ($courseid) {
    $context = context_course::instance($courseid);
} else {
    $context = context_system::instance();
}

require_capability('repository/sharedresources:view', $context);

// Prepare the page.

$PAGE->set_context($context);
$PAGE->set_pagelayout('course');
$PAGE->navbar->add(get_string('sharedresources_library', 'local_sharedresources'));
$PAGE->set_title(get_string('sharedresources_library', 'local_sharedresources'));
$PAGE->set_heading(get_string('sharedresources_library', 'local_sharedresources'));

$params = array('edit' => $edit,
                'blockaction' => $blockaction,
                'course' => $courseid,
                'repo' => $repo,
                'offset' => $offset,
                'what' => $action);
$PAGE->set_url('/local/sharedresources/index.php', $params);

$renderer = $PAGE->get_renderer('local_sharedresources');

$page = 20;

if ($action) {
    include($CFG->dirroot.'/local/sharedresources/index.controller.php');
}

$course = $DB->get_record('course', array('id' => $courseid));

$resourcesmoodlestr = get_string('resources', 'sharedresource');

if (empty($config->schema)) {
    print_error('nometadataplugin', 'sharedresource');
    die;
}

$visiblewidgets = array();
resources_setup_widgets($visiblewidgets, $context);
$searchfields = array();
if (resources_process_search_widgets($visiblewidgets, $searchfields)) {
    // If something has changed in filtering conditions, we might not have same resultset. Keep offset to 0.
    $offset = 0;
}

$regions = $PAGE->blocks->get_regions();

if (file_exists($CFG->dirroot.'/blocks/search')) {
    $block = block_instance('search');
    $bc = new block_contents();
    $bc->attributes['id'] = 'local_sharedresource_globalsearch_block';
    $bc->attributes['role'] = 'search';
    $bc->attributes['aria-labelledby'] = 'local_sharedresouces_search_title';
    $bc->title = html_writer::span(get_string('textsearch', 'local_sharedresources'), '', array('id' => 'local_sharedresources_globalsearch_title'));
    $bc->content = $block->get_content()->text;
    $PAGE->blocks->add_fake_block($bc, reset($regions));
}

$bc = new block_contents();
$bc->attributes['id'] = 'local_sharedresource_searchblock';
$bc->attributes['role'] = 'search';
$bc->attributes['aria-labelledby'] = 'local_sharedresouces_search_title';
$bc->title = html_writer::span(get_string('searchinlibrary', 'sharedresource'), '', array('id' => 'local_sharedresources_search_title'));
$bc->content = $renderer->search_widgets_tableless($courseid, $repo, $offset, $context, $visiblewidgets, $searchfields);
$PAGE->blocks->add_fake_block($bc, reset($regions));

$topkeywords = $renderer->top_keywords($courseid);
if (!empty($topkeywords)) {
    $bc = new block_contents();
    $bc->attributes['id'] = 'local_sharedresource_searchblock';
    $bc->attributes['role'] = 'search';
    $bc->attributes['aria-labelledby'] = 'local_sharedresouces_search_title';
    $bc->title = html_writer::span(get_string('topkeywords', 'local_sharedresources'), '', array('id' => 'local_sharedresources_topkeywords_title'));
    $bc->content = $topkeywords;
    $PAGE->blocks->add_fake_block($bc, reset($regions));
}

echo $OUTPUT->header();

echo $renderer->browse_tabs($repo, $course);

if (($repo == 'local') || empty($repo)) {
    echo $renderer->tools($course);
}

$isediting = has_capability('repository/sharedresources:manage', $context, $USER->id) && ($repo == 'local');

$fullresults = array();

$metadatafilters = array();
if (!empty($searchfields)) {
    foreach ($searchfields as $element => $search) {
        if (!empty($search)) {
            $metadatafilters[$element] = $search;
        }
    }
}

if ($repo == 'local') {
    $resources = get_local_resources($repo, $fullresults, $metadatafilters, $offset, $page);
} else {
    $resources = get_remote_repo_resources($repo, $fullresults, $metadatafilters, $offset, $page);
}

$SESSION -> resourceresult = $resources;

echo '<div id="resources">';
if ($fullresults['maxobjects'] <= $page) {
    // Do we have enough resource for one page ?
    echo $renderer->resources_list($resources, $course, $section, $isediting, $repo);
} else {
    $nbrpages = ceil($fullresults['maxobjects']/$page);
    echo $renderer->pager($courseid, $repo, $nbrpages, $page, $offset, $isediting);
    echo $renderer->resources_list($resources, $course, $section, $isediting, $repo, $page, $offset);
    echo $renderer->pager($courseid, $repo, $nbrpages, $page, $offset, $isediting);
}
echo '</div>';

echo '<center>';
echo '<br/>';
echo $renderer->browserlink();
echo '</center>';

if ($courseid > SITEID) {
    $options['id'] = $course->id;
    echo '<center><p>';
    $url = new moodle_url('/course/view.php', $options);
    print($OUTPUT->single_button($url, get_string('backtocourse', 'local_sharedresources')));
    echo '</p></center>';
}
 
echo $OUTPUT->footer();
