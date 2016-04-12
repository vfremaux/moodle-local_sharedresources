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
 * @package    local_sharedresources
 * @category   local
 * @author Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

require('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
require_once($CFG->dirroot.'/local/sharedresources/admin/admin_mass_import_form.php');
require_once($CFG->dirroot.'/local/sharedresources/lib.php');
require_once($CFG->dirroot.'/local/sharedresources/classes/import_processor.php');

$courseid = optional_param('course', SITEID, PARAM_INT);

$url = new moodle_url('/mod/sharedresource/admin_convertall.php');

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('coursemisconf');
}

// Security.

$systemcontext = context_system::instance();

if ($courseid > SITEID) {
    $context = context_course::instance($courseid);
    require_course_login($course);
    require_capability('repository/sharedresources:manage', $context);
    $PAGE->set_context($context);
} else {
    require_login();
    require_capability('repository/sharedresources:manage', $systemcontext);
    $PAGE->set_context($systemcontext);
}

$PAGE->set_title(get_string('resourceimport', 'local_sharedresources'));
$PAGE->set_heading(get_string('resourceimport', 'local_sharedresources'));
$PAGE->set_url($url, array('course' => $courseid));

// navigation
$PAGE->navbar->add(get_string('resourceimport', 'local_sharedresources'));
$PAGE->navbar->add(get_string('massimport', 'local_sharedresources'));

/// get courses

$form = new sharedresource_massimport_form($url, array('course' => $courseid));
$confirm = optional_param('confirm', '', PARAM_TEXT);
$killall = optional_param('killall', '', PARAM_BOOL);

if (has_capability('moodle/site:config', context_system::instance())) {
    if ($killall) {
        echo "Killing all resources";
        $DB->delete_records('sharedresource_entry', array());
        $DB->delete_records('sharedresource_metadata', array());
        $fs = get_file_storage();
        $fs->delete_area_files(1, 'mod_sharedresource');
    }
}

if ($confirm) {
    echo $OUTPUT->header();

    $data = new StdClass();
    $data->importpath = required_param('importpath', PARAM_TEXT);
    $data->importexclusionpattern = required_param('importexclusionpattern', PARAM_TEXT);
    $data->deducetaxonomyfrompath = required_param('deducetaxonomyfrompath', PARAM_BOOL);
    $data->context = required_param('context', PARAM_INT);

    // process import
    $importlist = array();
    sharedresources_scan_importpath($data->importpath, $importlist, $METADATA, $data);
    $importlist = sharedresources_aggregate($importlist, $METADATA);
    $processor = new import_processor();
    $processor->run($data, $importlist);

    echo $OUTPUT->continue_button($CFG->wwwroot.'/local/sharedresources/index.php?courseid='.$courseid);
    echo $OUTPUT->footer();
    die;
} elseif ($data = $form->get_data()) {

    if (isset($data->resetvolume)) {

        if (!is_dir($data->importpath)) {
            print_error('errornotadir', 'local_sharedresources', '', $CFG->dirroot.'/local/sharedresources/admin/admin_mass_import.php');
            return;
        }

        $result = sharedresources_reset_volume($data);
    } else {
        if (!is_dir($data->importpath)) {
            print_error('errornotadir', 'local_sharedresources', '', $CFG->dirroot.'/local/sharedresources/admin/admin_mass_import.php');
            return;
        }

        // scan target and report what will be imported
        echo $OUTPUT->header();

        $excludepattern = str_replace('\*', '.*', preg_quote($data->importexclusionpattern));

        $importlist = array();
        $METADATA = array();
        sharedresources_scan_importpath($data->importpath, $importlist, $METADATA, $data);

        echo $OUTPUT->heading(get_string('resourceimport', 'local_sharedresources'), 1);
        echo $OUTPUT->heading(get_string('filestoimport', 'local_sharedresources', $data->importpath), 2);
        echo '<pre>';
        foreach ($importlist as $entry) {
            echo "<b>$entry</b>\n";
            if (array_key_exists($entry, $METADATA)) {
                foreach ($METADATA[$entry] as $mtdkey => $mtdvalue) {
                    echo "\t{$mtdkey} => {$mtdvalue}\n";
                }
            }
        }
        echo '</pre>';    

        echo '<p><form action="#" name="confirm">';    
        echo '<input type="hidden" name="importpath" value="'.$data->importpath.'"/>';
        echo '<input type="hidden" name="context" value="'.$data->context.'"/>';
        echo '<input type="hidden" name="importexclusionpattern" value="'.@$data->importexclusionpattern.'"/>';
        echo '<input type="hidden" name="deducetaxonomyfrompath" value="'.@$data->deducetaxonomyfrompath.'"/>';
        echo '<input type="submit" name="confirm" value="'.get_string('confirm', 'local_sharedresources').'" />';
        echo '</form></p>';    

        echo $OUTPUT->footer();
        die;
    }
}

echo $OUTPUT->header();

if (!empty($result)) {
    echo $OUTPUT->box($result);
}
echo $OUTPUT->heading(get_string('resourceimport', 'local_sharedresources'), 1);
$form->display();

if (has_capability('moodle/site:config', context_system::instance())) {
    echo "<p><a href=\"{$CFG->wwwroot}/local/sharedresources/admin/admin_mass_import.php?killall=1\">Clean Everything Out</a></p>";
}
echo $OUTPUT->footer();
