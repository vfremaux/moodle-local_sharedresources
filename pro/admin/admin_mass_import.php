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
 * @category   local
 * @author     Valery Fremaux <valery.fremaux@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
require('../../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/mod/sharedresource/lib.php');
require_once($CFG->dirroot.'/mod/sharedresource/locallib.php');
require_once($CFG->dirroot.'/local/sharedresources/pro/admin/admin_mass_import_form.php');
require_once($CFG->dirroot.'/local/sharedresources/lib.php');
require_once($CFG->dirroot.'/local/sharedresources/pro/classes/import_processor.php');

$courseid = optional_param('course', SITEID, PARAM_INT);

$url = new moodle_url('/local/sharedresources/pro/admin/admin_mass_import.php', array('course' => $courseid));

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('coursemisconf');
}

// Security.

$systemcontext = context_system::instance();
$fs = get_file_storage();

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
$PAGE->set_url($url);

// Navigation.

$PAGE->navbar->add(get_string('resourceimport', 'local_sharedresources'));
$PAGE->navbar->add(get_string('massimport', 'local_sharedresources'));

$renderer = $PAGE->get_renderer('local_sharedresources');

// Get courses.

$form = new sharedresource_massimport_form($url, array('course' => $courseid));
$confirm = optional_param('confirm', '', PARAM_TEXT);
$killall = optional_param('killall', '', PARAM_BOOL);

$processor = new \local_sharedresources\importer\import_processor();

if (has_capability('moodle/site:config', context_system::instance())) {
    if ($killall) {
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
    $data->encoding = required_param('encoding', PARAM_TEXT);
    $data->context = required_param('context', PARAM_INT);
    $data->simulate = optional_param('simulate', false, PARAM_BOOL);
    $data->taxonomy = optional_param('taxonomy', false, PARAM_INT);
    $data->deployzips = optional_param('deployzips', false, PARAM_BOOL);
    $data->relocalize = optional_param('relocalize', false, PARAM_BOOL);
    $data->nativeutf8 = optional_param('nativeutf8', false, PARAM_BOOL);
    $data->makelabelsfromguidance = optional_param('makelabelsfromguidance', false, PARAM_BOOL);

    // Process import.

    $importlist = array();
    echo '<pre>';
    sharedresources_scan_importpath($data->importpath, $importlist, $metadatadefines, $data);
    echo '</pre>';

    echo '<pre>';
    $importlist = sharedresources_aggregate($importlist, $metadatadefines);
    echo '</pre>';

    echo '<pre>';
    $processor->run($data, $importlist);
    echo '</pre>';

    $buttonurl = new moodle_url('/local/sharedresources/index.php', array('courseid' => $courseid));
    echo $OUTPUT->continue_button($buttonurl);
    echo $OUTPUT->footer();
    die;

} else if ($data = $form->get_data()) {

    if (isset($data->resetvolume)) {

        if ($CFG->ostype == 'WINDOWS') {
            $data->importpath = str_replace('\\', '/', $data->importpath);
            $data->uimportpath = $data->importpath;
            $data->importpath = utf8_decode($data->uimportpath);
        }

        if (!is_dir($data->importpath)) {
            print_error('errornotadir', 'local_sharedresources', '', $url);
            exit;
        }

        $result = $processor->reset_volume($data);
        $returnurl = new moodle_url('/local/sharedresource/index.php');
        echo $OUTPUT->notification(get_string('resetdone', 'local_sharedresources'));
        echo $OUTPUT->continue_button($returnurl);
    } else {
        $prepared = false;
        if (!empty($data->uselocalpath)) {
            if (!is_dir($data->importpath)) {
                print_error('errornotadir', 'local_sharedresources', '', $url);
                exit;
            }

            if (!is_writable($data->importpath)) {
                print_error('errornotwritable', 'local_sharedresources', '', $url);
                exit;
            }

            $prepared = true;

        }
        if (empty($data->uselocalpath) && !empty($data->resourcearchive)) {

            // Receive the file.
            $usercontext = context_user::instance($USER->id);
            $draftitemid = file_get_submitted_draft_itemid('resourcearchive');
            $draftfiles =  $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, "itemid, filepath, filename", false);
            if (!empty($draftfiles)) {
                $archivefile = array_shift($draftfiles);

                $excludepattern = str_replace('\*', '.*', preg_quote($data->importexclusionpattern));

                // Prepare a temp folder with file extracted.
                $tempid = uniqid();
                $tempdir = make_temp_directory("sharedresource_import/$tempid");

                $zippacker = new zip_packer();
                $zippacker->extract_to_pathname($archivefile, $tempdir, null, null);
                $data->nativeutf8 = 1;

                // Switch import path to temp dir.
                $data->importpath = $tempdir;

                $prepared = true;
            }
        }

        if ($prepared == true) {
            // Scan target and report what will be imported.
            echo $OUTPUT->header();

            $excludepattern = str_replace('\*', '.*', preg_quote($data->importexclusionpattern));

            $importlist = array();
            $metadatadefines = array();
            echo '<pre>';
            sharedresources_scan_importpath($data->importpath, $importlist, $metadatadefines, $data);
            echo '</pre>';

            echo $OUTPUT->heading(get_string('resourceimport', 'local_sharedresources'), 1);
            echo $OUTPUT->heading(get_string('filestoimport', 'local_sharedresources', $data->importpath), 2);
            echo '<pre>';
            foreach ($importlist as $entry) {
                echo "<b>$entry</b>\n";
                if (array_key_exists($entry, $metadatadefines)) {
                    foreach ($metadatadefines[$entry] as $mtdkey => $mtdvalue) {
                        echo "\t{$mtdkey} => {$mtdvalue}\n";
                    }
                }
            }
            echo '</pre>';

            echo $renderer->confirm_import_form($data);

            echo $OUTPUT->footer();
            die;
        }
    }
}

echo $OUTPUT->header();

if (!empty($result)) {
    echo $OUTPUT->box($result);
}
echo $OUTPUT->heading(get_string('resourceimport', 'local_sharedresources'), 1);
$form->display();

if (has_capability('moodle/site:config', context_system::instance())) {
    $outurl = new moodle_url('/local/sharedresources/pro/admin/admin_mass_import.php', array('killall' => 1));
    echo '<p><a href="'.$outurl.'">'.get_string('cleaneverything', 'local_sharedresources').'</a><br>';
    echo get_string('cleaneverything_desc', 'local_sharedresources').'</p>';
}
echo $OUTPUT->footer();
