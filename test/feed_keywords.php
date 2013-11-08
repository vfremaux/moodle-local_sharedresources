<?php

/**
* This scripts converts all existing keywords in sharedresource_entries and feeds the metadata 
* records with suitable keyword records for known metadata schemes.
*
*
*/

	require_once "../../config.php";
	
	require_once $CFG->dirroot.'/mod/sharedresource/lib.php';
	
	// protect this script from non-admins
	require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));

	echo "<pre>";	
	mtrace("Setting keywords for all sharedresources...");
	if ($resources = $DB->get_records('sharedresource_entry',array('' => ''))){	
		foreach($resources as $entry){
			if (!empty($entry->keywords)){
				$plugins = sharedresource_get_plugins($entry->id); // hidden plugins are already discarded here
				foreach($plugins as $plugin){
					$plugin->setEntry($entry->id);
					if (method_exists($plugin, 'setKeywords')){
						mtrace("\tSetting keywords for entry {$entry->identifier} with {$entry->keywords}");
						$plugin->setKeywords($entry->keywords);
					}
				}
			}
		}
	}
	mtrace("Done.");
	echo "</pre>";	
