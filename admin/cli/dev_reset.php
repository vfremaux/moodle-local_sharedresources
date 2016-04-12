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
require_once($CFG->dirroot.'/local/sharedresources/lib.php');

$DB->delete_records('sharedresource_entry', array());
$DB->delete_records('sharedresource_metadata', array());
$DB->delete_records('sharedresource', array());
$DB->delete_records('course_modules', array('module' => 29));
$DB->delete_records('files', array('component' => 'mod_sharedresource'));

$data = new StdClass;
$data->importpath = 'D:/My Documents/Dossiers Actifs/ISF/Pool Ressources 2 prepared';
$result = sharedresources_reset_volume($data);
$data->importpath = 'D:/My Documents/Dossiers Actifs/ISF/Pool Ressources 1 prepared';
$result = sharedresources_reset_volume($data);

echo "Test data resetted";

