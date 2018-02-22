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
 * @author     Valery Fremaux <valery.fremaux@club-internet.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 1999 onwards Martin Dougiamas  http://dougiamas.com
 */
defined('MOODLE_INTERNAL') || die();

$string['accesscontrol'] = 'Access control';
$string['adminrepository'] = 'Admin Repository';
$string['backtocourse'] = 'Back to course';
$string['backtoindex'] = 'Back to index';
$string['boxviewalways'] = 'Always boxes';
$string['browse'] = 'Browse by categories';
$string['browser'] = 'Taxonomy based browser';
$string['choosetaxonomy'] = 'Taxonomy:&ensp;';
$string['cleaneverything'] = 'Clean all the library. Dangerous!)';
$string['clinonexistingcontext'] = 'Error: Non existing context';
$string['clinonexistingpath'] = 'Error: Path does not exist';
$string['configdefaultlibraryindexpage'] = 'Default index page of the library';
$string['configdefaulttaxonomyonimport'] = 'Default taxonomy for import';
$string['configdefaulttaxonomypurposeonedit'] = 'Taxonomy purpose for import';
$string['configlistviewthreshold'] = 'List view threshold';
$string['confignotfound'] = 'Config file not found';
$string['configprivatecatalog'] = 'Private catalog';
$string['confirm'] = 'Confirm';
$string['confirmsimulate'] = 'Confirm simulation';
$string['contains'] = 'contains';
$string['courselist'] = 'Course list';
$string['deducetaxonomyfrompath'] = 'Guess taxonomy from path';
$string['deployzips'] = 'Deploy zips';
$string['doresetvolume'] = 'Reset';
$string['emulatecommunity'] = 'Emulate the community version.';
$string['emulatecommunity_desc'] = 'Switches the code to the community version. The result will be more compatible, but some features will not be available anymore.';
$string['encoding'] = 'Metadata files encoding';
$string['endswith'] = 'ends with';
$string['equalto'] = 'equals to';
$string['errorinvalidresource'] = 'Invalid resource';
$string['errorinvalidresourceid'] = 'Invalid resource ID';
$string['errormnetpeer'] = 'MNET client initialisation error';
$string['errornotadir'] = 'The import directory does not exist or is not accessible';
$string['errornotwritable'] = 'the directory to be imported has no write permissions. Please change permissions before retrying.';
$string['exclusionpattern'] = 'Exclusion pattern';
$string['filestoimport'] = 'Files to import from {$a}';
$string['forcedelete'] = 'Force Delete (even if used)';
$string['importpath'] = 'Import path';
$string['importvolume'] = 'Import documents';
$string['installltitool'] = 'Install the LTI Tool';
$string['keywords'] = 'Mots clefs';
$string['library'] = 'Librairy';
$string['liked'] = 'Liked: ';
$string['listviewalways'] = 'Always list';
$string['makelabelsfromguidance'] = 'Make labels from guidance';
$string['markliked'] = 'I like it!';
$string['massimport'] = 'Mass import';
$string['newresource'] = 'Add a new resource';
$string['noaccess'] = 'You have not the required capabilities to access this service';
$string['noclassificationenabled'] = 'There is no classification enabled in this site. Administrator should configure the library with classifications, or disable this browser.';
$string['noentries'] = 'This repository has no resources';
$string['noresources'] = 'This repository has no local resources';
$string['notused'] = 'Never used in this site';
$string['plugindist'] = 'Plugin distribution';
$string['pluginname'] = 'Shared Resources Center';
$string['publish_sharedresource'] = 'Publish Sharedresource';
$string['purposediscriminator'] = 'Purpose discriminator';
$string['reinitialized'] = '{$a} files unmarked';
$string['relocalize'] = 'Relocalize';
$string['resetvolume'] = 'Reset volume';
$string['resourcearchive'] = 'Resources archive';
$string['resourceimport'] = 'Resource Import';
$string['resources'] = 'Resources';
$string['resourcesadministration'] = 'Resources administration';
$string['resourcespushout'] = 'Exporting to a provider library';
$string['rpcsharedresourceerror'] = 'RPC mod/sharedresource/get_list:<br/>{$a}';
$string['searchengine'] = 'Search engine';
$string['searchinlibrary'] = 'Search in library';
$string['sharedresources_library'] = 'Shared resources library';
$string['sharedresourcesindex'] = 'Shared resources index';
$string['simulate'] = 'Simulate';
$string['startswith'] = 'starts with';
$string['taxonomy'] = 'Taxonomy';
$string['textsearch'] = 'Full text search';
$string['topkeywords'] = 'Top keywords';
$string['updateresourcespageoff'] = 'Quit edit mode';
$string['updateresourcespageon'] = 'Edit this page';
$string['used'] = 'Used: {$a}';
$string['useimportpath'] = 'Use an uploaded file';
$string['uselocalpath'] = 'Use an internal system path';
$string['viewed'] = 'Viewed: {$a}';

$string['configdefaulttaxonomypurposeonedit_desc'] = 'The taxonomy purpose tells why the taxonomy is used for.
some metadata schema allows multiple taxonomies to be used at once for distinct purposes.';

$string['configdefaulttaxonomyonimport_desc'] = 'The taxonomy that should be fed by mass resources imports.';

$string['configprivatecatalog_desc'] = 'If checked, the sharedresource library is not accessible to unlogged users.
sharedresources indexs will NOT be harveastable by OAI endpoint';

$string['resetvolume_help'] = 'When processing, files are marked with a "__" prefix to allow respawn of the import if
memory or processing limits failure. You can reset the initial state of files using this option.';

$string['importpath_help'] = '
This path can point any location in the local server\'s filesystem.
Files should have been uploaded by an administrator in this location, and the location must be readable by the web server.
The whole directory will be scanned and all physical files will be indexed. MEtadata might be initialized from an optional
"metadata.csv" in each directory. This file will of course NOT be indexed itself.
';

$string['exclusionpattern_help'] = 'Filenames matching this pattern will NOT be indexed. The pattern admits wildcards (e.g. "*.jpg"
will ignore all JPEG images)';

$string['deducetaxonomyfrompath_help'] = 'If enabled, the relative path of the resource denotes the taxonomy. The discipline taxonomy
will be automatically be filled with taxonomy entries. The imported taxonomy hierarchy will reflect the directory organisation.';

$string['configdefaultlibraryindexpage_desc'] = 'The default index page choice may depend of the activated metadata plugin';

$string['purposediscriminator_help'] = 'You may give here a value for the taxmonomy purpose field that helps you to isolate this imported taxonomy
from other taxonomies in the sharedresource taxon table.';

$string['cleaneverything_desc'] = 'Will delete all resources entries and metadata in the library. shared resouce module instances in courses
will not be affected, but some references might be corrupted on reload.';

$string['resourcearchive_help'] = 'A ZIP archive with all resources organized in folders with metadata descriptors. See <a href="">the documentation</a>.';

$string['simulate_help'] = 'Will not write anything in moodle and keeps the import volume unchanged.';

$string['relocalize_help'] = 'Relocalize resources marked by a coursemoduletype colums set to "resource" (either implicit "sharedresource")';

$string['deployzips_help'] = 'If the file is a zip, and the resource is added to a course, then the zip will be unpacked in the resource course module storage.';

$string['makelabelsfromguidance_help'] = 'If the metadata.csv file contains a colomn "guidance", add labels course modules before the resources with the guidance text.';

$string['plugindist_desc'] = '
<p>This plugin is the community version and is published for anyone to use as is and check the plugin\'s
core application. A "pro" version of this plugin exists and is distributed under conditions to feed the life cycle, upgrade, documentation
and improvement effort.</p>
<p>Note that both components local_sharedresources and mod_sharedresource must work using the same distribution level.</p>
<p>Please contact one of our distributors to get "Pro" version support.</p>
<p><a href="http://www.mylearningfactory.com/index.php/documentation/Distributeurs?lang=en_utf8">MyLF Distributors</a></p>';

$string['configlistviewthreshold_desc'] = 'Number of resources in result over which display switches to list view.';
