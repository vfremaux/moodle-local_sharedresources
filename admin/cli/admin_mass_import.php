<?php

/**
* This script enables mass_importing of resources from the command line
* to avoid web timeouts on big importation volumes.
* import parameters should be provided using command line arguments
*/

define('CLI_SCRIPT', true);
define('CACHE_DISABLE_ALL', true);

require(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once($CFG->libdir.'/clilib.php');         // cli only functions
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/mod/sharedresource/lib.php');
require_once($CFG->dirroot . '/mod/sharedresource/locallib.php');
require_once($CFG->dirroot . '/local/sharedresources/admin/admin_mass_import_form.php');
require_once($CFG->dirroot . '/local/sharedresources/lib.php');
require_once($CFG->dirroot . '/local/sharedresources/classes/import_processor.php');

if (empty($CFG->pluginchoice)){
	$CFG->pluginchoice = 'lomfr';
}

$expectedoptions =     array(
        'path'   => true,
        'context'    => true,
        'exclude'    => true,
        'taxonomize' => false,
        'makelabelswithguidance' => false,
        'coursemoduletype' => true,
        'autodeploy' => false,
        'defaultmainfile' => true,
        'test' => true,
        'help' => false,
        'config' => true,
    );

// now get cli options
list($options, $unrecognized) = cli_get_params(
	$expectedoptions,
    array(
        'a' => 'autodeploy',
        'm' => 'coursemoduletype',
        'c' => 'config',
        'h' => 'help',
        'T' => 'taxonomize',
        't' => 'test',
    )
);

// $interactive = empty($options['non-interactive']);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Command line Moodle mass resource import.
Please note you must execute this script with the same uid as apache!

Options:
--path     					The absolute import path
--context      				Context ID to attach ressources entries to defaults to \"system\",
--exclude      				Exclusion pattern processed on entry names, simple wildcard (e.g. *.jpg),
--coursemoduletype  		The name of the final course module activity for resources, ('resource' or 'sharedresource'),
--makelabelsfromguidance  	If set, the \"guidance\" field of metadata will be used for making extra labels in course,
--autodeploy  				If set, all zip resources will be extracted, (only with \"resource\" course module type),
--defaultmainfile  			When zip is extracted, some filenames are searched for defaulting the \"main file\" entry point. Give a comma separated list.
--test     					Do not make real processing but simulates
-c, --config      			Defers all config to an external file,
-T, --taxonomize    		Enables taxonomy generation in default taxonomy purpose
-h, --help          		Print out this help

Example:
\$sudo -u www-data /usr/bin/php local/sharedresources/cli/mass_import.php
"; //TODO: localize - to be translated later when everything is finished

    echo $help;
    die;
}

// Get all options from config file
if (!empty($options['config'])){
	if (!file_exists($options['config'])){
    	cli_error(get_string('confignotfound', 'local_sharedresources'));
	}
	$content = file($options['config']);
	foreach($content as $l){
		if (preg_match('/^\s+$/', $l)) continue; // empty lines
		if (preg_match('/^[#\/!;]/', $l)) continue; // comments (any form)
		if (preg_match('/^(.*?)=(.*)$/', $l, $matches)) {
			if (in_array($matches[1], $expectedoptions)){
				$options[trim($matches[1])] = trim($matches[2]);
			}
		} 
	}
}

/// Here all config should be there. Reencode some related to filesystem encoding
$options['_path'] = $options['path'];
if ($CFG->ostype == 'WINDOWS'){
	$options['_path'] = utf8_encode($options['path']);
}

if (!is_dir($options['_path'])){
    cli_error(get_string('clinonexistingpath', 'local_sharedresources'));
    die;
}

if (empty($options['coursemoduletype'])){
	$options['coursemoduletype'] = 'sharedresource';
}

if (empty($options['autodeploy'])){
	$options['autodeploy'] = 0;
}

$systemcontext = context_system::instance();

if (empty($options['context'])){
	$options['context'] = $systemcontext->id;
} else {
	if (!$DB->get_record('context', array('id' => $options['context']))){
    	cli_error(get_string('clinonexistingcontext', 'local_sharedresources'));
	}
}

$data = new StdClass();
$data->importpath 				= $options['path'];
$data->importexclusionpattern 	= empty($options['exclude']) ? '' : $options['exclude'] ;
$data->deducetaxonomyfrompath 	= empty($options['taxonomize']) ? false : true ;
$data->context 					= $options['context'];

echo "OS Type is : ".$CFG->ostype."\n";

// process import
$importlist = array();
sharedresources_scan_importpath($data->importpath, $importlist, $METADATA, $data);
$importlist = sharedresources_aggregate($importlist, $METADATA);
if ($options['test'] == 'listonly'){
	print_object($importlist);
} else {

	// this passes some options to defines so we can catch them deeper in the implementation.
	if ($options['test'] == 'simulate'){
		define('DO_NOT_WRITE', 1);
	}

	if (array_key_exists('makelabelsfromguidance', $options)){
		define('MAKE_LABELS_FROM_GUIDANCE', 1);
	}

	if ($options['coursemoduletype'] == 'resource'){
		define('CONVERT_TO_RESOURCE', 1);
	}

	if (array_key_exists('autodeploy', $options)){
		define('AUTO_DEPLOY', 1);
	}

	if (array_key_exists('defaultmainfile', $options)){
		define('DEFAULT_MAIN_FILES', $options['defaultmainfile']);
	} else {
		define('DEFAULT_MAIN_FILES', '%FILENAME%.htm,index.htm,default.htm,');
	}

	$processor = new import_processor();
	echo "Starting processor\n";
	$processor->run($data, $importlist);
}


