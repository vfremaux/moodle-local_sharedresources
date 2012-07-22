<?php

/**
*
*/

    $ADMIN->add('root', new admin_category('resources', get_string('resources', 'resources', '', $CFG->dirroot.'/resources/lang/')));

    $plugins = get_list_of_plugins('resources/plugins');
    foreach($plugins as $plugin){
        if (file_exists($CFG->dirroot.'/resources/plugins/'.$plugin.'/settings.php')){
            // each plugin shoud add its proper page
            include $CFG->dirroot.'/resources/plugins/'.$plugin.'/settings.php';
        }
    }
    
?>