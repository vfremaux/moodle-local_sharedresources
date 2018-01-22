<?php
<<<<<<< HEAD

/**
* this class overheads the import process as a whole
*
*/

require_once $CFG->dirroot.'/local/sharedresources/classes/file_importer_base.php';

class import_processor{
	
	function run($formdata, $inputlist){
		
		if (!is_dir($formdata->importpath)){
			print_error('errornotadir', 'local_sharedresources', '', $CFG->dirroot.'/local/sharedresources/admin/admin_mass_import.php');
			return;
		}
		
		if (!$FILE = fopen($formdata->importpath.'/moodle_sharedlibrary_import.log', 'w')){
			print_error('errornotwritablevolume', 'local_sharedresources', '', $CFG->dirroot.'/local/sharedresources/admin/admin_mass_import.php');
			return;
		}
		fclose($FILE);
		
		foreach($inputlist as $entry => $importdescriptor){
			$importer = new file_importer_base($importdescriptor);
			$importer->make_resource_entry($formdata->context);
			$importer->metadata_preprocess();
			$importer->aggregate_metadata();
			$importer->save();
			
			// final cleanup
			$fs = get_file_storage();
			$fs->delete_area_files(1, 'mod_sharedresources', 'temp');
		}

		unlink($formdata->importpath.'/moodle_sharedlibrary_import.log');		
	}

=======
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

require_once($CFG->dirroot.'/local/sharedresources/classes/file_importer_base.php');

class import_processor {

    public function run($formdata, $inputlist) {

        if (!is_dir($formdata->importpath)) {
            print_error('errornotadir', 'local_sharedresources', '', $CFG->dirroot.'/local/sharedresources/admin/admin_mass_import.php');
            return;
        }

        if (!$file = fopen($formdata->importpath.'/moodle_sharedlibrary_import.log', 'w')) {
            print_error('errornotwritablevolume', 'local_sharedresources', '', $CFG->dirroot.'/local/sharedresources/admin/admin_mass_import.php');
            return;
        }
        fclose($file);

        foreach ($inputlist as $entry => $importdescriptor) {
            $importer = new file_importer_base($importdescriptor);
            $importer->make_resource_entry($formdata->context);
            $importer->metadata_preprocess();
            $importer->aggregate_metadata();
            $importer->save();
            $importer->attach();

            // Final cleanup.
            $fs = get_file_storage();
            $fs->delete_area_files(1, 'mod_sharedresources', 'temp');
        }

        unlink($formdata->importpath.'/moodle_sharedlibrary_import.log');
    }
>>>>>>> MOODLE_33_STABLE
}