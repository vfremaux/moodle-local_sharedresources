<?php
<<<<<<< HEAD

$string['adminrepository'] = 'Admin Repository';
$string['backtocourse'] = 'Back to course';
$string['configdefaulttaxonomypurposeonimport'] = 'The taxonomy purpose tells why the taxonomy is used for. some metadata schema allows multiple taxonomies to be used at once for distinct purposes.';
$string['confirm'] = 'Confirm';
$string['deducetaxonomyfrompath'] = 'Guess taxonomy from path';
$string['clinonexistingpath'] = 'Error: Path does not exist';
$string['clinonexistingcontext'] = 'Error: Non existing context';
$string['deducetaxonomyfrompath_help'] = 'If enabled, the relative path of the resource denotes the taxonomy. The discipline taxonomy will be automatically be filled with taxonomy entries.';
$string['defaulttaxonomypurposeonimport'] = 'Taxonomy purpose for import';
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

$string['adminrepository'] = 'Admin Repository';
$string['backtocourse'] = 'Back to course';
$string['backtoindex'] = 'Back to index';
$string['browse'] = 'Browse by categories';
$string['browser'] = 'Taxonomy based browser';
$string['clinonexistingcontext'] = 'Error: Non existing context';
$string['clinonexistingpath'] = 'Error: Path does not exist';
$string['configdefaulttaxonomypurposeonimport'] = 'Taxonomy purpose for import';
$string['configdefaultlibraryindexpage'] = 'Default index page of the library';
$string['confignotfound'] = 'Config file not found';
$string['configprivatecatalog'] = 'Private catalog';
$string['confirm'] = 'Confirm';
$string['courselist'] = 'Course list';
$string['deducetaxonomyfrompath'] = 'Guess taxonomy from path';
>>>>>>> MOODLE_33_STABLE
$string['doresetvolume'] = 'Reset';
$string['errorinvalidresource'] = 'Invalid resource';
$string['errorinvalidresourceid'] = 'Invalid resource ID';
$string['errormnetpeer'] = 'MNET client initialisation error';
$string['errornotadir'] = 'The import directory does not exist or is not accessible';
$string['exclusionpattern'] = 'Exclusion pattern';
<<<<<<< HEAD
$string['exclusionpattern_help'] = 'Filenames matching this pattern will NOT be indexed. The pattern admits wildcards (e.g. "*.jpg" will ignore all JPEG images)';
$string['filestoimport'] = 'Files to import from {$a}';
$string['forcedelete'] = 'Force Delete (even if used)';
$string['importpath'] = 'Import path';
$string['installltitool'] = 'Install the LTI Tool';
$string['keywords'] = 'Mots clefs';
$string['liked'] = 'Liked: {$a}';
=======
$string['filestoimport'] = 'Files to import from {$a}';
$string['forcedelete'] = 'Force Delete (even if used)';
$string['importpath'] = 'Import path';
$string['importvolume'] = 'Import documents';
$string['installltitool'] = 'Install the LTI Tool';
$string['keywords'] = 'Mots clefs';
$string['library'] = 'Librairy';
$string['liked'] = 'Liked: ';
>>>>>>> MOODLE_33_STABLE
$string['markliked'] = 'I like it!';
$string['massimport'] = 'Mass import';
$string['newresource'] = 'Add a new resource';
$string['noresources'] = 'This repository has no local resources';
<<<<<<< HEAD
=======
$string['notused'] = 'Never used in this site';
>>>>>>> MOODLE_33_STABLE
$string['pluginname'] = 'Shared Resources Center';
$string['publish_sharedresource'] = 'Publish Sharedresource';
$string['reinitialized'] = '{$a} files unmarked';
$string['resetvolume'] = 'Reset volume';
$string['resourceimport'] = 'Resource Import';
$string['resources'] = 'Resources';
$string['resourcesadministration'] = 'Resources administration';
$string['resourcespushout'] = 'Exporting to a provider library';
<<<<<<< HEAD
$string['sharedresources_library'] = 'Shared resources library';
=======
$string['rpcsharedresourceerror'] = 'RPC mod/sharedresource/get_list:<br/>{$a}';
$string['searchinlibrary'] = 'Search in library';
$string['searchengine'] = 'Search engine';
$string['sharedresources_library'] = 'Shared resources library';
$string['sharedresourcesindex'] = 'Shared resources index';
$string['textsearch'] = 'Full text search';
>>>>>>> MOODLE_33_STABLE
$string['topkeywords'] = 'Top keywords';
$string['updateresourcespageoff'] = 'Quit edit mode';
$string['updateresourcespageon'] = 'Edit this page';
$string['used'] = 'Used: {$a}';
$string['viewed'] = 'Viewed: {$a}';

<<<<<<< HEAD
$string['resetvolume_help'] = 'When processing, files are marked with a "__" prefix to allow respawn of the import if memory or processing limits failure. You can reset the initial state of files using this option.';
=======
$string['configdefaulttaxonomypurposeonimport_desc'] = 'The taxonomy purpose tells why the taxonomy is used for.
some metadata schema allows multiple taxonomies to be used at once for distinct purposes.';

$string['configprivatecatalog_desc'] = 'If checked, the sharedresource library is not accessible to unlogged users.
sharedresources indexs will NOT be harveastable by OAI endpoint';

$string['resetvolume_help'] = 'When processing, files are marked with a "__" prefix to allow respawn of the import if
memory or processing limits failure. You can reset the initial state of files using this option.';
>>>>>>> MOODLE_33_STABLE

$string['importpath_help'] = '
This path can point any location in the local server\'s filesystem.
Files should have been uploaded by an administrator in this location, and the location must be readable by the web server.
<<<<<<< HEAD
The whole directory will be scanned and all physical files will be indexed. MEtadata might be initialized from an optional "metadata.csv" in each directory. This file will of course
NOT be indexed itself.
';
=======
The whole directory will be scanned and all physical files will be indexed. MEtadata might be initialized from an optional
"metadata.csv" in each directory. This file will of course NOT be indexed itself.
';

$string['exclusionpattern_help'] = 'Filenames matching this pattern will NOT be indexed. The pattern admits wildcards (e.g. "*.jpg"
will ignore all JPEG images)';

$string['deducetaxonomyfrompath_help'] = 'If enabled, the relative path of the resource denotes the taxonomy. The discipline taxonomy
will be automatically be filled with taxonomy entries. The imported taxonomy hierarchy will reflect the directory organisation.';

$string['configdefaultlibraryindexpage_desc'] = 'The default index page choice may depend of the activated metadata plugin';
>>>>>>> MOODLE_33_STABLE
