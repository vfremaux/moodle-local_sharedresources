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

$config = get_config('local_sharedresources');

$courseid = optional_param('course', false, PARAM_INT);

// hidden key to open the catalog to the unlogged area.
if (!empty($config->privatecatalog)) {

    if ($courseid) {
        $context = context_course::instance($courseid);
    } else {
        $context = context_system::instance();
    }
    require_login();
    require_capability('repository/sharedresources:view', $context);
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

$navigator = new \local_sharedresources\browser\navigation();

$classificationfilters = $navigator->get_category_filters();

$i = 0;
foreach ($classificationfilters as $afilter) {
    $options = $navigator->get_filter_modalities($filter);
    $filters["f$i"] = new StdClass;
    $filters["f$i"]->name = $afilter->name;
    $filters["f$i"]->options = $options;
    $filters["f$i"]->value = optional_param("f$i", '', PARAM_INT);
    $i++;
}

echo $OUTPUT->header();
echo $OUTPUT->heading($strheading, 2);

if (is_dir($CFG->dirroot.'/local/staticguitexts')) {
    // If static gui texts are installed, add a static text to be edited by administrator.
    echo '<div class="static">';
    local_print_static_text('sharedresources_browser_header', $CFG->wwwroot.'/local/sharedresources/browser.php');
    echo '</div>';
}

// Making filters.

echo $renderer->filters($catid, $catpath);

// Calling navigation.

if ($catid) {
    $category = $navigator->get_category($catid, $catpath, $filters);
    echo $renderer->category($category, $catpath, $navigator->count_entries_rec($category), 'current', true);

    // Root of the catalog cannot have resourses.
    echo $renderer->resourcelist(array_keys($cattree->entries));
}

echo $renderer->children($cattree, $catpath);

echo $renderer->searchlink();

echo $OUTPUT->Footer();