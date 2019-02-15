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
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */

/**
 * this class overheads the import process as a whole
 */
namespace local_sharedresources\importer;

require_once($CFG->dirroot.'/local/sharedresources/pro/classes/file_importer_base.php');

class import_processor {

    public function run($formdata, $inputlist) {

        if (!is_dir($formdata->importpath)) {
            print_error('errornotadir', 'local_sharedresources', '', new moodle_url('/local/sharedresources/pro/admin/admin_mass_import.php'));
            return;
        }

        if (!$logfile = fopen($formdata->importpath.'/moodle_sharedlibrary_import.log', 'w')) {
            print_error('errornotwritablevolume', 'local_sharedresources', '', new moodle_url('/local/sharedresources/pro/admin/admin_mass_import.php'));
            return;
        }

        foreach ($inputlist as $entry => $importdescriptor) {
            $importer = new file_importer_base($importdescriptor, (array) $formdata);
            $importer->set_log_file($logfile);
            $importer->make_resource_entry($formdata->context);
            $importer->metadata_preprocess();
            $importer->aggregate_metadata();
            $importer->save();
            $importer->attach();

            // Final cleanup.
            $fs = get_file_storage();
            $fs->delete_area_files(1, 'mod_sharedresources', 'temp');
        }
    }

    /**
     * In all the code, $_ variable contain filesystem compatible encodings, other
     * are all UTF8 variable
     */
    public function reset_volume($data) {
        global $CFG;

        $upath = $data->importpath;
        if ($CFG->ostype == 'WINDOWS') {
            $path = utf8_decode($upath);
        } else {
            $path = $upath;
        }

        if (file_exists($path.'/moodle_sharedlibrary_import.log')) {
            unlink ($path.'/moodle_sharedlibrary_import.log');
        }
        $r = 0;
        $this->reset_volume_rec($upath, $r);

        return get_string('reinitialized', 'local_sharedresources', $r);
    }

    /**
     * In all the code, $_ variable contain filesystem compatible encodings, other
     * are all UTF8 variable
     */
    private function reset_volume_rec($upath, &$r) {
        global $CFG;

        if ($CFG->ostype == 'WINDOWS') {
            $path = utf8_decode($upath);
        } else {
            $path = $upath;
        }

        if (!is_dir($path)) {
            mtrace("Not existant dir $upath... skipping");
            return;
        }

        $dir = opendir($path);
        while ($entry = readdir($dir)) {
            if (preg_match('/^\\./', $entry)) {
                continue;
            }
            if (preg_match('/(CVS|SVN)/', $entry)) {
                continue;
            }

            if ($CFG->ostype == 'WINDOWS') {
                $uentry = utf8_encode($entry);
            } else {
                $uentry = $entry;
            }

            if (is_dir($path.'/'.$entry)) {
                $this->reset_volume_rec($upath.'/'.$uentry, $r);
            } else {
                if (preg_match('/^__(.*)/', $entry, $matches)) {
                    $unmarked = $matches[1];
                    rename($path.'/'.$entry, $path.'/'.$unmarked);
                    $r++;
                }
            }
        }
        closedir($dir);
    }
}