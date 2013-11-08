<?php

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

}