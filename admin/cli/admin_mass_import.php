<?php

/**
* This script enables mass_importing of resources from the command line
* to avoid web timeouts on big importation volumes.
* import parameters should be provided using command line arguments
*/

define('CLI_SCRIPT', true);
define('CACHE_DISABLE_ALL', true);

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/clilib.php');         // cli only functions
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/mod/sharedresource/lib.php');
require_once($CFG->dirroot . '/mod/sharedresource/locallib.php');
require_once($CFG->dirroot . '/local/sharedresources/admin/admin_mass_import_form.php');
require_once($CFG->dirroot . '/local/sharedresources/lib.php');
require_once($CFG->dirroot . '/local/sharedresources/classes/import_processor.php');

// now get cli options
list($options, $unrecognized) = cli_get_params(
    array(
        'path'   => true,
        'context'    => true,
        'exclude'    => true,
        'taxonomize' => false,
        'help'              => false
    ),
    array(
        'h' => 'help',
        'T' => 'taxonomize'
    )
);

$interactive = empty($options['non-interactive']);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
"Command line Moodle mass resource import.
Please note you must execute this script with the same uid as apache!

Options:
--path     			The absolute import path
--context      		Context ID to attach ressources entries to defaults to \"system\",
--exclude      		Exclusion pattern processed on entry names, simple wildcard (e.g. *.jpg),
-T, --taxonomize    Enables taxonomy generation in default taxonomy purpose
-h, --help          Print out this help

Example:
\$sudo -u www-data /usr/bin/php local/sharedresources/cli/mass_import.php
"; //TODO: localize - to be translated later when everything is finished

    echo $help;
    die;
}

if (!is_dir($options['path'])){
    cli_error(get_string('clinonexistingpath', 'local_sharedresources'));
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

// process import
$importlist = array();
sharedresources_scan_importpath($data->importpath, $importlist, $METADATA, $data);
$importlist = sharedresources_aggregate($importlist, $METADATA);
$processor = new import_processor();
$processor->run($data, $importlist);


