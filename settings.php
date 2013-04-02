<?php

require_once $CFG->dirroot.'/local/sharedresources/lib.php';

/**
*
*/
    $ADMIN->add('root', new admin_category('resources', get_string('resources', 'local_sharedresources')));
    
    $settings = new admin_settingpage('local_sharedresources', get_string('pluginname', 'sharedresource'));
  
    $plugins = get_list_of_plugins('local/sharedresources/plugins');
    foreach($plugins as $plugin){
        if (file_exists($CFG->dirroot.'/local/sharedresources/plugins/'.$plugin.'/settings.php')){
            // each plugin shoud add its proper page
            include $CFG->dirroot.'/local/sharedresources/plugins/'.$plugin.'/settings.php';
        }
    }
    
    $ADMIN->add('localplugins', $settings);
    
?>