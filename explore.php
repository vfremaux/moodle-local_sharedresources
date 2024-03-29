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
require_once($CFG->dirroot.'/local/sharedresources/classes/search_widget.class.php');
if (is_dir($CFG->dirroot.'/local/staticguitexts')) {
    require_once($CFG->dirroot.'/local/staticguitexts/lib.php');
}

// DO not rely on moodle classloader.
if ($searchplugins = glob($CFG->dirroot.'/local/sharedresources/classes/searchwidgets/*')) {
    foreach ($searchplugins as $sp) {
        include_once($sp);
    }
}

require_once($CFG->dirroot.'/local/sharedresources/lib.php');

define('RETURN_PAGE', 2);

$config = get_config('local_sharedresources');
$shrconfig = get_config('sharedresource');
$mtdplugin = sharedresource_get_plugin($shrconfig->schema);

$edit = optional_param('edit', -1, PARAM_BOOL);
$blockaction = optional_param('blockaction', '', PARAM_ALPHA);
$courseid = optional_param('course', SITEID, PARAM_INT); // Optional course if we are comming from a course.
$section = optional_param('section', '', PARAM_INT); // Optional course section if we are searhcing for feeding a section.
$repo = optional_param('repo', 'local', PARAM_TEXT);
$offset = optional_param('offset', 0, PARAM_INT);
$action = optional_param('what', '', PARAM_TEXT);
$mode = optional_param('mode', 'full', PARAM_TEXT);

$PAGE->requires->js_call_amd('local_sharedresources/library', 'init', ['courseid' => $courseid]);
$PAGE->requires->js_call_amd('local_sharedresources/search', 'init');
$PAGE->requires->js_call_amd('local_sharedresources/boxview', 'init');

// Security.

$context = context_system::instance();
if (!empty($config->privatecatalog)) {
    if ($courseid) {
        $context = context_course::instance($courseid);
        $course = $DB->get_record('course', array('id' => $courseid));
        require_login($course);
    } else {
        $context = context_system::instance();
    }

    if (!sharedresources_has_capability_somewhere('repository/sharedresources:view', false, false, false, CONTEXT_COURSECAT.','.CONTEXT_COURSE)) {
        throw new moodle_exception(get_string('noaccess', 'local_sharedresource'));
    }
}

// Prepare the page.

$PAGE->set_context($context);
$PAGE->navbar->add(get_string('sharedresources_library', 'local_sharedresources'));
$PAGE->set_title(get_string('sharedresources_library', 'local_sharedresources'));
$PAGE->set_heading(get_string('sharedresources_library', 'local_sharedresources'));
$PAGE->set_pagelayout('standard');

$params = array('edit' => $edit,
                'blockaction' => $blockaction,
                'course' => $courseid,
                'repo' => $repo,
                'offset' => $offset,
                'mode' => $mode,
                'what' => $action);
$PAGE->set_url('/local/sharedresources/explore.php', $params);

$renderer = $PAGE->get_renderer('local_sharedresources');

$page = 20;

if ($action) {
    include($CFG->dirroot.'/local/sharedresources/library.controller.php');
}

$course = $DB->get_record('course', array('id' => $courseid));

if (empty($config->searchblocksposition)) {
    set_config('searchblocksposition', 'side-pre', 'local_sharedresources');
    $config->searchblocksposition = 'side-pre';
}

$resourcesmoodlestr = get_string('resources', 'sharedresource');

if (file_exists($CFG->dirroot.'/blocks/search') && get_config('local_search', 'enable')) {
    $configsaved = $config;
    $block = block_instance('search');
    $bc = new block_contents();
    $bc->attributes['id'] = 'local_sharedresource_globalsearch_block';
    $bc->attributes['role'] = 'search';
    $bc->attributes['aria-labelledby'] = 'local_sharedresouces_search_title';
    $args = array('id' => 'local_sharedresources_globalsearch_title');
    $bc->title = html_writer::span(get_string('textsearch', 'local_sharedresources'), '', $args);
    $bc->content = $block->get_content()->text;
    $config = $configsaved; // Bring back the local_sharedresource config that has been tweaked by the search block loading.
    $PAGE->blocks->add_fake_block($bc, $config->searchblocksposition);
}

/* Search in sharedresources */

$visiblewidgets = [];
if ($repo == 'local') {
    sharedresources_setup_widgets($visiblewidgets, $context);
} else {
    $visiblewidgets = sharedresources_remote_widgets($repo, $context);
}

// find search fields, values, and eventual changes in filtering request.
// If mode is single, will build relevant searchfield to search text in.
$searchfields = [];
if (sharedresources_process_search_widgets($mtdplugin, $visiblewidgets, $searchfields, $mode)) {
    // If something has changed in filtering conditions, we might not have same resultset. Keep offset to 0.
    $offset = 0;
}

$layout = 'tableless';
switch ($mode) {
    case 'simple' : {
        $layout = 'singlefield';
        break;
    }
    case 'full' : {
        $layout = 'tableless';
        break;
    }
}

$bc = new block_contents();
$bc->attributes['id'] = 'local_sharedresource_searchblock';
$bc->attributes['role'] = 'search';
$bc->attributes['aria-labelledby'] = 'local_sharedresouces_search_title';
$args = array('id' => 'local_sharedresources_search_title');
$bc->title = html_writer::span(get_string('searchinlibrary', 'local_sharedresources'), '', $args);
// $bc->content = $renderer->search_widgets_tableless($courseid, $repo, $offset, $context, $visiblewidgets, $searchfields);
$bc->content = $renderer->search_block($courseid, $repo, $offset, $context, $visiblewidgets, $searchfields, $layout);
$PAGE->blocks->add_fake_block($bc, $config->searchblocksposition);

$topkeywords = $renderer->top_keywords($courseid);
if (!empty($topkeywords)) {
    $bc = new block_contents();
    $bc->attributes['id'] = 'local_sharedresource_searchblock';
    $bc->attributes['role'] = 'search';
    $bc->attributes['aria-labelledby'] = 'local_sharedresouces_search_title';
    $args = array('id' => 'local_sharedresources_topkeywords_title');
    $bc->title = html_writer::span(get_string('topkeywords', 'local_sharedresources'), '', $args);
    $bc->content = $topkeywords;
    $PAGE->blocks->add_fake_block($bc, $config->searchblocksposition);
}

echo $OUTPUT->header();

if (local_sharedresources_supports_feature('repo/remote')) {
    echo $renderer->browse_tabs($repo, $course);
}

if (($repo == 'local') || empty($repo)) {
    echo $renderer->tools($course);
}

$levels = CONTEXT_COURSECAT.','.CONTEXT_COURSE;
$isediting = sharedresources_has_capability_somewhere('repository/sharedresources:create', false, false, false, $levels);

$fullresults = [];

if ($repo == 'local' || !local_sharedresources_supports_feature('repo/remote')) {
    $resources = sharedresources_get_local_resources($repo, $fullresults, $searchfields, $offset, $page);
} else {
    $resources = sharedresources_get_remote_repo_resources($repo, $fullresults, $searchfields, $offset, $page);
}

$SESSION->resourceresult = $resources;

if (is_object($mtdplugin) && $mtdplugin->getTaxonomyValueElement()) {
    // Only browse if there is a taxonomy in the metadata schema.
    echo '<center>';
    echo '<br/>';
    echo $renderer->browserlink();
    echo '</center>';
}

$shrclass = ($isediting) ? 'is-editing' : '';
echo '<div id="resources" class="'.$shrclass.'">';

if (empty($resources)) {
    echo $OUTPUT->notification(get_string('noresources', 'local_sharedresources'));
} else {
    if ($fullresults['maxobjects'] <= $page) {
        // Do we have enough resource for one page ?
        echo $renderer->resources_list($resources, $course, $section, $isediting, $repo);
    } else {
        $nbrpages = ceil($fullresults['maxobjects'] / $page);
        echo $renderer->pager($courseid, $repo, $nbrpages, $page, $offset, $isediting);
        echo $renderer->resources_list($resources, $course, $section, $isediting, $repo, $page, $offset);
        echo $renderer->pager($courseid, $repo, $nbrpages, $page, $offset, $isediting);
    }
}
echo '</div>';

if (is_object($mtdplugin) && $mtdplugin->getTaxonomyValueElement()) {
    // Only browse if there is a taxonomy in the metadata schema.
    echo '<center>';
    echo '<br/>';
    echo $renderer->browserlink();
    echo '</center>';
}

if ($courseid > SITEID) {
    $options['id'] = $course->id;
    echo '<center><p>';
    $url = new moodle_url('/course/view.php', $options);
    print($OUTPUT->single_button($url, get_string('backtocourse', 'local_sharedresources')));
    echo '</p></center>';
}

echo $OUTPUT->footer();
