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
 * This script enables mass_importing of resources from the command line
 * to avoid web timeouts on big importation volumes.
 * import parameters should be provided using command line arguments
 */

define('CLI_SCRIPT', true);
define('CACHE_DISABLE_ALL', true);

require(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config.php');
require_once($CFG->libdir.'/clilib.php');         // Cli only functions.
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

