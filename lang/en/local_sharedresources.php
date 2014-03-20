<?php

$string['adminrepository'] = 'Admin Repository';
$string['backtocourse'] = 'Back to course';
$string['configdefaulttaxonomypurposeonimport'] = 'The taxonomy purpose tells why the taxonomy is used for. some metadata schema allows multiple taxonomies to be used at once for distinct purposes.';
$string['confirm'] = 'Confirm';
$string['confignotfound'] = 'Config file not found';
$string['deducetaxonomyfrompath'] = 'Guess taxonomy from path';
$string['clinonexistingpath'] = 'Error: Path does not exist';
$string['clinonexistingcontext'] = 'Error: Non existing context';
$string['deducetaxonomyfrompath_help'] = 'If enabled, the relative path of the resource denotes the taxonomy. The discipline taxonomy will be automatically be filled with taxonomy entries.';
$string['defaulttaxonomypurposeonimport'] = 'Taxonomy purpose for import';
$string['doresetvolume'] = 'Reset';
$string['errorinvalidresource'] = 'Invalid resource';
$string['errorinvalidresourceid'] = 'Invalid resource ID';
$string['errormnetpeer'] = 'MNET client initialisation error';
$string['errornotadir'] = 'The import directory does not exist or is not accessible';
$string['exclusionpattern'] = 'Exclusion pattern';
$string['exclusionpattern_help'] = 'Filenames matching this pattern will NOT be indexed. The pattern admits wildcards (e.g. "*.jpg" will ignore all JPEG images)';
$string['filestoimport'] = 'Files to import from {$a}';
$string['forcedelete'] = 'Force Delete (even if used)';
$string['importpath'] = 'Import path';
$string['installltitool'] = 'Install the LTI Tool';
$string['keywords'] = 'Mots clefs';
$string['liked'] = 'Liked: {$a}';
$string['markliked'] = 'I like it!';
$string['massimport'] = 'Mass import';
$string['newresource'] = 'Add a new resource';
$string['noresources'] = 'This repository has no local resources';
$string['pluginname'] = 'Shared Resources Center';
$string['publish_sharedresource'] = 'Publish Sharedresource';
$string['reinitialized'] = '{$a} files unmarked';
$string['resetvolume'] = 'Reset volume';
$string['resourceimport'] = 'Resource Import';
$string['resources'] = 'Resources';
$string['resourcesadministration'] = 'Resources administration';
$string['resourcespushout'] = 'Exporting to a provider library';
$string['sharedresources_library'] = 'Shared resources library';
$string['topkeywords'] = 'Top keywords';
$string['updateresourcespageoff'] = 'Quit edit mode';
$string['updateresourcespageon'] = 'Edit this page';
$string['used'] = 'Used: {$a}';
$string['viewed'] = 'Viewed: {$a}';

$string['resetvolume_help'] = 'When processing, files are marked with a "__" prefix to allow respawn of the import if memory or processing limits failure. You can reset the initial state of files using this option.';

$string['importpath_help'] = '
This path can point any location in the local server\'s filesystem.
Files should have been uploaded by an administrator in this location, and the location must be readable by the web server.
The whole directory will be scanned and all physical files will be indexed. MEtadata might be initialized from an optional "metadata.csv" in each directory. This file will of course
NOT be indexed itself.
';
