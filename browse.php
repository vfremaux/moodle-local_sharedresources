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
 * @package    local_sharedresources
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 *
 */
require('../../config.php');
require_once($CFG->dirroot.'/local/sharedresources/classes/navigator.class.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/local/sharedresources/lib.php');

$PAGE->requires->js_call_amd('local_sharedresources/boxview', 'init');

$config = get_config('local_sharedresources');

$courseid = optional_param('course', false, PARAM_INT);
$section = optional_param('section', 0, PARAM_INT);

if ($courseid) {
    if (!$course = $DB->get_record('course', array('id' => $courseid))) {
        print_error('coursemisconf');
    }
} else {
    // Site level browsing.
    $course = new StdClass;
    $course->id = SITEID;
}

// hidden key to open the catalog to the unlogged area.
$context = context_system::instance();

if (!empty($config->privatecatalog)) {

    if ($courseid) {
        $context = context_course::instance($courseid);
        require_login($course);
    } else {
        $context = context_system::instance();
        require_login();
    }
    $caps = array('repository/sharedresources:use','repository/sharedresources:create', 'repository/sharedresources:manage');
    if (!sharedresources_has_capability_somewhere('repository/sharedresources:view', false, false, false, CONTEXT_COURSECAT.','.CONTEXT_COURSE)) {
        if (!has_any_capability($caps, $context)) {
            print_error('noaccess', 'local_sharedresource');
        }
    }
}

$catid = optional_param('catid', '', PARAM_INT);
$catpath = optional_param('catpath', '', PARAM_RAW);

$strheading = get_string('sharedresourcesindex', 'local_sharedresources');

$url = new moodle_url('/local/sharedresources/browse.php');
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->navbar->add($strheading);
$PAGE->navbar->add(get_string('browse', 'local_sharedresources'));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('animatenumber', 'local_sharedresources');

$PAGE->set_heading($strheading);
$PAGE->set_title($strheading);

$renderer = $PAGE->get_renderer('local_sharedresources');

$filters = null;

// Getting all filters.

try {
    $taxonomyselector = $renderer->taxonomy_select();
    $taxonomyobj = $DB->get_record('sharedresource_classif', array('id' => $SESSION->sharedresources->taxonomy));
    $navigator = new \local_sharedresources\browser\navigation($taxonomyobj);
} catch (Exception $e) {
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading, 2);
    echo $OUTPUT->notification(get_string('noclassificationenabled', 'local_sharedresources'));

    echo $renderer->searchlink();

    echo $OUTPUT->footer();
    die;
}

// $classificationfilters = $navigator->get_category_filters();

$i = 0;
/*
foreach ($classificationfilters as $afilter) {
    $options = $navigator->get_filter_modalities($filter);
    $filters["f$i"] = new StdClass;
    $filters["f$i"]->name = $afilter->name;
    $filters["f$i"]->options = $options;
    $filters["f$i"]->value = optional_param("f$i", '', PARAM_INT);
    $i++;
}
*/
$filters = null;

echo $OUTPUT->header();

echo $renderer->tools($course);

if (is_dir($CFG->dirroot.'/local/staticguitexts')) {
    // If static gui texts are installed, add a static text to be edited by administrator.
    echo '<div class="static">';
    local_print_static_text('sharedresources_browser_header', $CFG->wwwroot.'/local/sharedresources/browse.php');
    echo '</div>';
}

// Making filters.

// echo $renderer->filters($catid, $catpath);

echo $taxonomyselector;

// Calling navigation.

$isediting = has_capability('repository/sharedresources:manage', $context, $USER->id);

if ($catid) {
    $category = $navigator->get_category($catid, $catpath, $filters);
    echo $renderer->category($category, $catpath, $navigator->count_entries_rec($catpath), 'current', true);

    // Root of the catalog cannot have resources.
    $category->cats = $navigator->get_children($catid);
    echo $renderer->resources_list($category->entries, $course, $section, $isediting);
} else {
    $category = new StdClass;
    $catid = 0;
    $category->cats = $navigator->get_children($catid);
    $category->hassubs = count($category->cats);
}

echo $renderer->children($category, $catpath);

echo '<center>';
echo $renderer->searchlink();
echo '</center>';

echo $OUTPUT->Footer();