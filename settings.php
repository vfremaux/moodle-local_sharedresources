<?php

require_once $CFG->dirroot.'/local/sharedresources/lib.php';
require_once($CFG->dirroot.'/mod/sharedresource/plugins/'.$CFG->pluginchoice.'/plugin.class.php');
require_once($CFG->dirroot.'/mod/sharedresource/metadatalib.php');

/**
*
*/

if ($hassiteconfig) { // needs this condition or there is error on login page

    $ADMIN->add('root', new admin_category('resources', get_string('resources', 'local_sharedresources')));
    
    $settings = new admin_settingpage('local_sharedresources', get_string('pluginname', 'sharedresource'));

	if (isset($CFG->pluginchoice)){
		$object = 'sharedresource_plugin_'.$CFG->pluginchoice;
		$mtdstandard = new $object;

		$purposes = array();
		$purposefield = $mtdstandard->getTaxonomyPurposeElement();
		foreach($purposefield->values as $purpose){
			$purposes[$purpose] = get_string(clean_string_key($purpose), 'sharedresource');
		}
    
	    $settings->add(new admin_setting_configselect('defaulttaxonomypurposeonimport', get_string('defaulttaxonomypurposeonimport', 'local_sharedresources'), get_string('configdefaulttaxonomypurposeonimport', 'local_sharedresources'), 0, $purposes));
	}
  
    $plugins = get_list_of_plugins('local/sharedresources/plugins');
    foreach($plugins as $plugin){
        if (file_exists($CFG->dirroot.'/local/sharedresources/plugins/'.$plugin.'/settings.php')){
            // each plugin shoud add its proper page
            include $CFG->dirroot.'/local/sharedresources/plugins/'.$plugin.'/settings.php';
        }
    }
    
    $ADMIN->add('localplugins', $settings);
}
?>